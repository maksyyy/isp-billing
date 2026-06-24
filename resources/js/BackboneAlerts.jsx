import React, { useEffect, useState, useRef } from "react";
import axios from "axios";
import PrtgMonitoringPanel from "./PrtgMonitoringPanel";
import MikrotikMonitoringPanel from "./MikrotikMonitoringPanel";


// Helper to clean raw HTML tags from PRTG
const cleanHtml = (str) => {
    if (!str) return "";
    return String(str).replace(/<[^>]*>/g, "").trim();
};

export default function BackboneAlerts({ role }) {
    const [devices, setDevices] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
    const [modalOpen, setModalOpen] = useState(false);
    const [editingDevice, setEditingDevice] = useState(null);
    
    // Form states
    const [name, setName] = useState("");
    const [ip, setIp] = useState("");
    const [formError, setFormError] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Monitoring Panels States
    const [dashboardData, setDashboardData] = useState({});
    const [prtgData, setPrtgData] = useState([]);
    const [mikrotikData, setMikrotikData] = useState(null);
    const [loadingMikrotik, setLoadingMikrotik] = useState(false);

    // Traffic Monitoring States & Refs
    const [selectedIface, setSelectedIface] = useState(null);
    const [trafficHistory, setTrafficHistory] = useState({ times: [], rx: [], tx: [] });

    const prevBytesRef = useRef({}); // { name: { rx, tx } }
    const prevTimestampRef = useRef(null);
    const selectedIfaceRef = useRef(selectedIface);

    // Keep selected interface ref in sync on every state update
    useEffect(() => {
        selectedIfaceRef.current = selectedIface;
    }, [selectedIface]);

    // PRTG Helper Functions
    const getDeviceStatus = (d) => {
        if (!d) return "unknown";
        const raw = Number(d.status_raw);
        const statusStr = d.status ? String(d.status).toLowerCase() : "";
        
        if (raw === 3 || statusStr.includes("up")) return "online";
        if (raw === 4 || raw === 10 || statusStr.includes("warning") || statusStr.includes("unusual")) return "warning";
        if ([5, 13, 14].includes(raw) || statusStr.includes("down")) return "down";
        if ([7, 8, 9, 11, 12].includes(raw) || statusStr.includes("paused")) return "paused";
        return "unknown";
    };

    const getDurationSince = (oleDate) => {
        if (!oleDate) return "tidak diketahui";
        const epochOffset = 25569; // Selisih hari antara 1899-12-30 dan 1970-01-01
        const msInDay = 86400000;
        const statusChangeTime = new Date((Number(oleDate) - epochOffset) * msInDay);
        const now = new Date();
        const diffMs = now - statusChangeTime;
        
        if (diffMs < 0) return "0 menit";
        
        const diffMins = Math.floor(diffMs / 60000);
        if (diffMins < 60) {
            return `${diffMins} menit`;
        }
        
        const diffHours = Math.floor(diffMins / 60);
        const mins = diffMins % 60;
        if (diffHours < 24) {
            return `${diffHours} jam ${mins} menit`;
        }
        
        const diffDays = Math.floor(diffHours / 24);
        const hours = diffHours % 24;
        return `${diffDays} hari ${hours} jam`;
    };

    const getDeviceDuration = (d) => {
        if (!d) return "tidak diketahui";
        const status = getDeviceStatus(d);
        
        if (status === "down") {
            return getDurationSince(d.lastup_raw);
        } else if (status === "online") {
            return getDurationSince(d.lastdown_raw);
        } else {
            const rawTime = d.lastup_raw || d.lastdown_raw;
            return rawTime ? getDurationSince(rawTime) : "tidak diketahui";
        }
    };

    const fetchDevices = () => {
        axios.get("/api/backbone-devices")
            .then(res => {
                setDevices(res.data || []);
            })
            .catch(err => {
                console.error("Error fetching backbone devices:", err);
            })
            .finally(() => {
                setLoading(false);
            });
    };

    const fetchMonitoringData = () => {
        // 1. Fetch Dashboard KPI
        axios.get("/api/dashboard-data")
            .then(res => setDashboardData(res.data || {}))
            .catch(err => console.error("Error dashboard-data fetch:", err));

        // 2. Fetch PRTG
        axios.get("/api/prtg")
            .then(res => setPrtgData(res.data?.sensors || []))
            .catch(err => console.error("Error prtg fetch:", err));

        // 3. Fetch MikroTik resources + interface list (tanpa traffic bytes — cukup 1 kali di awal)
        setLoadingMikrotik(true);
        axios.get("/api/mikrotik/dashboard-data")
            .then(res => {
                const newMData = res.data || {};
                setMikrotikData(newMData);

                // Set default interface yang dipilih (hanya saat pertama kali)
                if (newMData.connected && newMData.interfaces?.length > 0) {
                    if (!selectedIfaceRef.current) {
                        const firstIface = newMData.interfaces[0];
                        setSelectedIface(firstIface);
                    }
                }
            })
            .catch(err => console.error("Error mikrotik fetch:", err))
            .finally(() => setLoadingMikrotik(false));
    };

    // Fetch traffic untuk 1 interface yang sedang dipilih saja
    const fetchSelectedInterfaceTraffic = () => {
        const iface = selectedIfaceRef.current;
        if (!iface?.name) return;

        axios.get(`/api/mikrotik/interface-traffic?name=${encodeURIComponent(iface.name)}`)
            .then(res => {
                if (res.data && res.data.rx_byte !== undefined) {
                    handleTrafficCalculation([res.data]);
                }
            })
            .catch(err => console.error("Error interface-traffic fetch:", err));
    };

    const handleTrafficCalculation = (interfacesList) => {
        const now = Date.now();
        const prevTimestamp = prevTimestampRef.current;
        const timeDiffSeconds = prevTimestamp ? (now - prevTimestamp) / 1000 : 5;

        // Calculate speed in Mbps for each interface
        const calculatedSpeeds = {};
        interfacesList.forEach(iface => {
            const name = iface.name;
            const prev = prevBytesRef.current[name] || { rx: iface.rx_byte, tx: iface.tx_byte };
            
            const rxDiff = iface.rx_byte - prev.rx;
            const txDiff = iface.tx_byte - prev.tx;

            const rxMbps = rxDiff >= 0 ? ((rxDiff * 8) / 1000000) / timeDiffSeconds : 0;
            const txMbps = txDiff >= 0 ? ((txDiff * 8) / 1000000) / timeDiffSeconds : 0;

            calculatedSpeeds[name] = { rx: rxMbps, tx: txMbps };
        });

        // Save bytes for the next calculation in Ref to avoid re-renders
        const newPrevBytes = {};
        interfacesList.forEach(iface => {
            newPrevBytes[iface.name] = { rx: iface.rx_byte, tx: iface.tx_byte };
        });

        prevBytesRef.current = newPrevBytes;
        prevTimestampRef.current = now;

        // Update selected interface
        const activeIfaceName = selectedIfaceRef.current?.name || interfacesList[0]?.name || null;
        const activeIface = interfacesList.find(i => i.name === activeIfaceName) || interfacesList[0] || null;
        
        if (activeIface) {
            const speed = calculatedSpeeds[activeIface.name] || { rx: 0, tx: 0 };
            const timeLabel = new Date().toLocaleTimeString("id-ID", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit"
            }) + " WIB";

            setTrafficHistory(prev => {
                const times = [...prev.times, timeLabel].slice(-15);
                const rx = [...prev.rx, speed.rx].slice(-15);
                const tx = [...prev.tx, speed.tx].slice(-15);
                return { times, rx, tx };
            });
        }
    };

    useEffect(() => {
        // Fetch awal — interface list & semua data monitoring
        fetchDevices();
        fetchMonitoringData();

        // Polling ringan: backbone devices setiap 10 detik
        const deviceInterval = setInterval(() => {
            fetchDevices();
        }, 10000);

        // Polling berat: dashboard + PRTG + MikroTik resource setiap 30 detik
        const monitorInterval = setInterval(() => {
            fetchMonitoringData();
        }, 30000);

        return () => {
            clearInterval(deviceInterval);
            clearInterval(monitorInterval);
        };
    }, []);

    // Polling traffic interface yang dipilih — setiap 5 detik, hanya 1 interface
    useEffect(() => {
        if (!selectedIface) return;

        // Reset history saat ganti interface
        setTrafficHistory({ times: [], rx: [], tx: [] });
        prevBytesRef.current = {};
        prevTimestampRef.current = null;

        // Langsung fetch pertama kali
        fetchSelectedInterfaceTraffic();

        const trafficInterval = setInterval(() => {
            fetchSelectedInterfaceTraffic();
        }, 5000);

        return () => clearInterval(trafficInterval);
    }, [selectedIface?.name]);

    const openAddModal = () => {
        setEditingDevice(null);
        setName("");
        setIp("");
        setFormError("");
        setModalOpen(true);
    };

    const openEditModal = (device) => {
        setEditingDevice(device);
        setName(device.name);
        setIp(device.ip);
        setFormError("");
        setModalOpen(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (!name.trim()) {
            setFormError("Nama perangkat wajib diisi.");
            return;
        }
        
        const ipPattern = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        if (!ipPattern.test(ip)) {
            setFormError("Format IP Address tidak valid.");
            return;
        }

        setIsSubmitting(true);
        setFormError("");

        const payload = { name, ip };
        const request = editingDevice
            ? axios.put(`/api/backbone-devices/${editingDevice.id}`, payload)
            : axios.post("/api/backbone-devices", payload);

        request
            .then(() => {
                setModalOpen(false);
                fetchDevices();
            })
            .catch(err => {
                const errMsg = err.response?.data?.message || "Gagal menyimpan perangkat.";
                setFormError(errMsg);
            })
            .finally(() => {
                setIsSubmitting(false);
            });
    };

    const handleDelete = (id, deviceName) => {
        if (confirm(`Apakah Anda yakin ingin menghapus perangkat backbone "${deviceName}"?`)) {
            axios.delete(`/api/backbone-devices/${id}`)
                .then(() => {
                    fetchDevices();
                })
                .catch(err => {
                    console.error("Error deleting device:", err);
                    alert("Gagal menghapus perangkat backbone.");
                });
        }
    };

    const filteredDevices = devices.filter(d => 
        d.name.toLowerCase().includes(search.toLowerCase()) || 
        d.ip.includes(search)
    );

    const prtgCustomers = prtgData.filter((d) => d && d.device && /^\d+/.test(String(d.device)));

    return (
        <main style={appContainer}>
            <style>{`
                .modal-anim { animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
                @keyframes modalFadeIn {
                    from { opacity: 0; transform: translateY(20px) scale(0.95); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }
                .btn-hover {
                    transition: all 0.2s ease-in-out !important;
                }
                .btn-hover:hover {
                    transform: translateY(-2px) !important;
                    filter: brightness(1.1) !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                }
                .btn-hover:active {
                    transform: translateY(0) !important;
                }
                @keyframes pulseGreen {
                    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
                    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
                    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
                }
                .pulse-green {
                    animation: pulseGreen 2s infinite !important;
                }
                @keyframes pulseRed {
                    0% { transform: scale(0.85); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
                    70% { transform: scale(1.1); box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
                    100% { transform: scale(0.85); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
                }
                .pulse-red {
                    animation: pulseRed 1.5s infinite !important;
                }
            `}</style>


            {/* Header Area */}
            <header style={headerStyle}>
                <div>
                    <h2 style={titleStyle}>🚨 Sub-second Alerts (Backbone Monitor)</h2>
                    <p style={{ margin: "5px 0 0", color: "#64748b", fontSize: "14px" }}>
                        Pantau status perangkat backbone secara real-time. Notifikasi instan akan dikirimkan ke Telegram saat status berubah.
                    </p>
                </div>

                <div style={{ display: "flex", gap: "10px", alignItems: "center" }}>
                    <div style={{ display: "flex", alignItems: "center", gap: "6px", background: "#f1f5f9", padding: "6px 12px", borderRadius: "10px" }}>
                        <span style={livePulseDot} className="pulse-green"></span>
                        <span style={{ fontSize: "12px", color: "#64748b", fontWeight: "600" }}>Live Checking</span>
                    </div>
                    
                    <button 
                        type="button" 
                        onClick={openAddModal} 
                        style={btnAdd}
                        className="btn-hover"
                    >
                        ➕ Tambah Perangkat
                    </button>
                </div>
            </header>

            {/* Toolbar: Search */}
            <div style={toolbarStyle}>
                <input 
                    type="text" 
                    placeholder="Cari nama atau IP perangkat..." 
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    style={searchInputStyle}
                />
                <div style={{ fontSize: "13px", color: "#64748b" }}>
                    Menampilkan <strong>{filteredDevices.length}</strong> perangkat
                </div>
            </div>

            {/* Table Area */}
            <div style={tableContainerStyle}>
                {loading ? (
                    <div style={{ padding: "40px", textAlign: "center", color: "#64748b" }}>
                        ⏳ Memuat data perangkat backbone...
                    </div>
                ) : filteredDevices.length === 0 ? (
                    <div style={{ padding: "50px", textAlign: "center", color: "#64748b", background: "#fff", borderRadius: "16px", border: "1px dashed #cbd5e1" }}>
                        🖥️ Belum ada perangkat backbone yang terdaftar atau cocok dengan pencarian.
                    </div>
                ) : (
                    <table style={{ width: "100%", borderCollapse: "collapse", fontSize: "14px", textAlign: "left" }}>
                        <thead>
                            <tr style={{ background: "#f1f5f9", borderBottom: "2px solid #cbd5e1" }}>
                                <th style={{ padding: "12px 16px", color: "#475569" }}>Nama Perangkat</th>
                                <th style={{ padding: "12px 16px", color: "#475569" }}>IP Address</th>
                                <th style={{ padding: "12px 16px", color: "#475569" }}>Status</th>
                                <th style={{ padding: "12px 16px", color: "#475569" }}>Waktu Cek Terakhir</th>
                                <th style={{ padding: "12px 16px", color: "#475569", textAlign: "right" }}>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredDevices.map(d => {
                                const isUp = d.status === "up";
                                const badgeStyle = {
                                    fontSize: "11px",
                                    padding: "4px 10px",
                                    borderRadius: "999px",
                                    fontWeight: "bold",
                                    display: "inline-flex",
                                    alignItems: "center",
                                    gap: "6px",
                                    background: isUp ? "#d1fae5" : "#fee2e2",
                                    color: isUp ? "#065f46" : "#991b1b"
                                };

                                return (
                                    <tr key={d.id} style={{ borderBottom: "1px solid #e2e8f0", background: "#fff" }}>
                                        <td style={{ padding: "16px", fontWeight: "600", color: "#1e293b" }}>
                                            🖥️ {d.name}
                                        </td>
                                        <td style={{ padding: "16px", fontFamily: "monospace", color: "#334155" }}>
                                            {d.ip}
                                        </td>
                                        <td style={{ padding: "16px" }}>
                                            <span style={badgeStyle}>
                                                <span 
                                                    style={isUp ? statusPulseGreen : statusPulseRed} 
                                                    className={isUp ? "pulse-green" : "pulse-red"}
                                                ></span>
                                                {d.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td style={{ padding: "16px", color: "#64748b" }}>
                                            🕒 {d.last_ping_at 
                                                ? new Date(d.last_ping_at).toLocaleString("id-ID")
                                                : "Belum pernah dicek"}
                                        </td>
                                        <td style={{ padding: "16px", textAlign: "right" }}>
                                            <div style={{ display: "flex", gap: "8px", justifyContent: "flex-end" }}>
                                                <button 
                                                    type="button" 
                                                    onClick={() => openEditModal(d)} 
                                                    style={btnEdit}
                                                    className="btn-hover"
                                                >
                                                    ✏️ Edit
                                                </button>
                                                <button 
                                                    type="button" 
                                                    onClick={() => handleDelete(d.id, d.name)} 
                                                    style={btnDelete}
                                                    className="btn-hover"
                                                >
                                                    🗑️ Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                )}
            </div>

            {/* MikroTik Monitoring Panel — dengan tab Traffic terintegrasi */}
            <MikrotikMonitoringPanel 
                data={mikrotikData} 
                loading={loadingMikrotik}
                interfaces={mikrotikData?.interfaces || []}
                selectedInterface={selectedIface}
                onSelectInterface={(iface) => setSelectedIface(iface)}
                trafficHistory={trafficHistory}
            />


            {/* PRTG Monitoring Panel */}
            {prtgData.length > 0 && (
                <PrtgMonitoringPanel 
                    prtgCustomers={prtgCustomers}
                    getDeviceStatus={getDeviceStatus}
                    getDeviceDuration={getDeviceDuration}
                />
            )}

            {/* Modal Box for Add / Edit Device */}
            {modalOpen && (
                <div style={overlay} onClick={() => setModalOpen(false)}>
                    <div 
                        style={modalBox} 
                        className="modal-anim" 
                        onClick={(e) => e.stopPropagation()}
                    >
                        <h3 style={{ margin: "0 0 15px 0", fontSize: "18px", fontWeight: "700" }}>
                            {editingDevice ? "✏️ Edit Perangkat Backbone" : "➕ Tambah Perangkat Backbone"}
                        </h3>

                        {formError && (
                            <div style={errorBoxStyle}>
                                ⚠️ {formError}
                            </div>
                        )}

                        <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: "15px" }}>
                            <div style={{ display: "flex", flexDirection: "column", gap: "5px" }}>
                                <label style={labelStyle}>Nama Perangkat</label>
                                <input 
                                    type="text" 
                                    placeholder="Contoh: Router Core Gedung A" 
                                    value={name} 
                                    onChange={(e) => setName(e.target.value)}
                                    style={modalInputStyle}
                                />
                            </div>

                            <div style={{ display: "flex", flexDirection: "column", gap: "5px" }}>
                                <label style={labelStyle}>IP Address</label>
                                <input 
                                    type="text" 
                                    placeholder="Contoh: 192.168.1.1" 
                                    value={ip} 
                                    onChange={(e) => setIp(e.target.value)}
                                    style={modalInputStyle}
                                />
                            </div>

                            <div style={{ display: "flex", gap: "10px", marginTop: "10px" }}>
                                <button 
                                    type="button" 
                                    onClick={() => setModalOpen(false)} 
                                    style={btnCancel}
                                    className="btn-hover"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    disabled={isSubmitting} 
                                    style={btnSubmit}
                                    className="btn-hover"
                                >
                                    {isSubmitting ? "Menyimpan..." : "Simpan"}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </main>
    );
}

/* Style Definitions */
const appContainer = {
    padding: "30px",
    maxWidth: "1200px",
    margin: "0 auto",
    color: "#111111",
    fontFamily: "'Geist Sans', -apple-system, sans-serif"
};

const headerStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: "30px",
    paddingBottom: "15px",
    borderBottom: "1px solid #EAEAEA"
};

const titleStyle = {
    margin: 0,
    fontSize: "36px",
    fontWeight: "400",
    fontFamily: "'Instrument Serif', serif",
    fontStyle: "italic",
    color: "#111111"
};

const livePulseDot = {
    width: "8px",
    height: "8px",
    borderRadius: "50%",
    background: "#346538",
    display: "inline-block"
};

const statusPulseGreen = {
    width: "6px",
    height: "6px",
    borderRadius: "50%",
    background: "#346538",
    display: "inline-block"
};

const statusPulseRed = {
    width: "6px",
    height: "6px",
    borderRadius: "50%",
    background: "#9F2F2D",
    display: "inline-block"
};

const toolbarStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: "20px",
    gap: "15px",
    flexWrap: "wrap"
};

const searchInputStyle = {
    padding: "8px 14px",
    borderRadius: "8px",
    border: "1px solid #EAEAEA",
    fontSize: "14px",
    outline: "none",
    width: "300px",
    background: "#fff",
    color: "#111111"
};

const tableContainerStyle = {
    background: "#fff",
    borderRadius: "8px",
    border: "1px solid #EAEAEA",
    overflow: "hidden",
    marginBottom: "30px"
};

const btnAdd = {
    padding: "8px 16px",
    background: "#111111",
    color: "#fff",
    border: "none",
    borderRadius: "6px",
    cursor: "pointer",
    fontWeight: "600",
    fontSize: "13px",
    transition: "all 0.15s ease"
};

const btnEdit = {
    padding: "6px 12px",
    background: "white",
    color: "#111111",
    border: "1px solid #EAEAEA",
    borderRadius: "6px",
    cursor: "pointer",
    fontWeight: "600",
    fontSize: "12px",
    transition: "all 0.15s ease"
};

const btnDelete = {
    padding: "6px 12px",
    background: "white",
    color: "#9F2F2D",
    border: "1px solid #EAEAEA",
    borderRadius: "6px",
    cursor: "pointer",
    fontWeight: "600",
    fontSize: "12px",
    transition: "all 0.15s ease"
};

const overlay = {
    position: "fixed",
    top: 0,
    left: 0,
    width: "100%",
    height: "100%",
    background: "rgba(0, 0, 0, 0.2)",
    backdropFilter: "blur(2px)",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    zIndex: 999
};

const modalBox = {
    background: "#fff",
    padding: "25px",
    borderRadius: "8px",
    border: "1px solid #EAEAEA",
    width: "90%",
    maxWidth: "450px",
    boxShadow: "0 4px 20px rgba(0,0,0,0.03)",
};

const labelStyle = {
    fontSize: "11px",
    fontWeight: "700",
    color: "#787774",
    textTransform: "uppercase",
    letterSpacing: "0.05em"
};

const modalInputStyle = {
    padding: "8px 12px",
    borderRadius: "6px",
    border: "1px solid #EAEAEA",
    fontSize: "14px",
    outline: "none"
};

const btnCancel = {
    padding: "8px 16px",
    background: "white",
    color: "#787774",
    border: "1px solid #EAEAEA",
    borderRadius: "6px",
    cursor: "pointer",
    fontWeight: "600",
    flex: 1,
    textAlign: "center",
    transition: "all 0.15s ease"
};

const btnSubmit = {
    padding: "8px 16px",
    background: "#111111",
    color: "#fff",
    border: "none",
    borderRadius: "6px",
    cursor: "pointer",
    fontWeight: "600",
    flex: 1,
    textAlign: "center",
    transition: "all 0.15s ease"
};

const errorBoxStyle = {
    padding: "12px",
    border: "1px solid #FDEBEC",
    borderRadius: "6px",
    background: "#FDEBEC",
    color: "#9F2F2D",
    fontSize: "13px",
    marginBottom: "15px"
};
