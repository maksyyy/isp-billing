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
        <main className="py-6 max-w-7xl mx-auto text-[#FAF9F6] font-sans">
            <style>{`
                .modal-anim { animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
                @keyframes modalFadeIn {
                    from { opacity: 0; transform: translateY(20px) scale(0.95); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
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
            <header className="flex justify-between items-center mb-8 pb-4 border-b border-[#222226]">
                <div>
                    <h2 className="m-0 text-3xl font-bold font-heading text-[#FAF9F6] tracking-tight">Sub-second Alerts (Backbone Monitor)</h2>
                    <p className="margin-0 mt-1 text-xs text-[#8E8E90] uppercase tracking-wider font-semibold">
                        Pantau status perangkat backbone secara real-time. Notifikasi instan akan dikirimkan ke Telegram saat status berubah.
                    </p>
                </div>

                <div className="flex gap-2.5 items-center">
                    <div className="flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#0C2D1F]/50 border border-[#10B981]/20 text-[9px] text-[#10B981] font-bold uppercase tracking-wider">
                        <span className="w-1.5 h-1.5 rounded-full bg-[#10B981] pulse-green"></span>
                        <span>Live Checking</span>
                    </div>
                    
                    <button 
                        type="button" 
                        onClick={openAddModal} 
                        className="btn-minimal px-4 py-2.5"
                    >
                        Tambah Perangkat
                    </button>
                </div>
            </header>

            {/* Toolbar: Search */}
            <div className="flex justify-between items-center mb-5 gap-4 flex-wrap">
                <input 
                    type="text" 
                    placeholder="Cari nama atau IP perangkat..." 
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="w-72 text-xs py-2 bg-[#0B0B0D] border border-[#222226] focus:border-[#FAF9F6]/40 focus:ring-0 rounded-md text-[#FAF9F6] transition-all"
                />
                <div className="text-xs text-[#8E8E90]">
                    Menampilkan <strong className="text-[#FAF9F6]">{filteredDevices.length}</strong> perangkat
                </div>
            </div>

            {/* Table Area */}
            <div className="app-card overflow-hidden mb-8">
                {loading ? (
                    <div className="p-10 text-center text-[#8E8E90] text-sm">
                        Memuat data perangkat backbone...
                    </div>
                ) : filteredDevices.length === 0 ? (
                    <div className="p-12 text-center text-[#8E8E90] text-sm app-card border-dashed">
                        Belum ada perangkat backbone yang terdaftar atau cocok dengan pencarian.
                    </div>
                ) : (
                    <table className="app-table">
                        <thead>
                            <tr>
                                <th>Nama Perangkat</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Waktu Cek Terakhir</th>
                                <th style={{ textAlign: "right" }}>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredDevices.map(d => {
                                const isUp = d.status === "up";
                                return (
                                    <tr key={d.id}>
                                        <td className="font-semibold text-[#FAF9F6]">
                                            {d.name}
                                        </td>
                                        <td className="font-mono text-xs">
                                            {d.ip}
                                        </td>
                                        <td>
                                            <span className={isUp ? "status-badge-active" : "status-badge-inactive"}>
                                                <span 
                                                    className={`w-1.5 h-1.5 rounded-full inline-block mr-1.5 ${isUp ? 'bg-[#10B981] pulse-green' : 'bg-[#EF4444] pulse-red'}`}
                                                ></span>
                                                {d.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td>
                                            {d.last_ping_at 
                                                ? new Date(d.last_ping_at).toLocaleString("id-ID")
                                                : "Belum pernah dicek"}
                                        </td>
                                        <td style={{ textAlign: "right" }}>
                                            <div className="flex gap-2 justify-end">
                                                <button 
                                                    type="button" 
                                                    onClick={() => openEditModal(d)} 
                                                    className="btn-minimal-secondary px-3 py-1.5 text-[10px]"
                                                >
                                                    Edit
                                                </button>
                                                <button 
                                                    type="button" 
                                                    onClick={() => handleDelete(d.id, d.name)} 
                                                    className="btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] px-3 py-1.5 text-[10px]"
                                                >
                                                    Hapus
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
                <div className="fixed inset-0 bg-[#050505]/80 backdrop-blur-sm flex items-center justify-center z-50" onClick={() => setModalOpen(false)}>
                    <div 
                        className="bg-[#0C0C0E]/95 border border-[#222226] p-6 rounded-md w-full max-w-md shadow-2xl modal-anim" 
                        onClick={(e) => e.stopPropagation()}
                    >
                        <h3 className="margin-0 mb-4 text-lg font-bold text-[#FAF9F6]">
                            {editingDevice ? "Edit Perangkat Backbone" : "Tambah Perangkat Backbone"}
                        </h3>

                        {formError && (
                            <div className="p-3 border border-[#EF4444]/20 rounded-md bg-[#EF4444]/10 text-[#EF4444] text-xs mb-4">
                                {formError}
                            </div>
                        )}

                        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                            <div className="flex flex-col gap-1.5">
                                <label className="text-[10px] font-bold text-[#8E8E90] uppercase tracking-wider">Nama Perangkat</label>
                                <input 
                                    type="text" 
                                    placeholder="Contoh: Router Core Gedung A" 
                                    value={name} 
                                    onChange={(e) => setName(e.target.value)}
                                    className="w-full text-sm p-2 bg-[#0B0B0D] border border-[#222226] focus:border-[#FAF9F6]/40 focus:ring-0 rounded-md text-[#FAF9F6] transition-all"
                                />
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <label className="text-[10px] font-bold text-[#8E8E90] uppercase tracking-wider">IP Address</label>
                                <input 
                                    type="text" 
                                    placeholder="Contoh: 192.168.1.1" 
                                    value={ip} 
                                    onChange={(e) => setIp(e.target.value)}
                                    className="w-full text-sm p-2 bg-[#0B0B0D] border border-[#222226] focus:border-[#FAF9F6]/40 focus:ring-0 rounded-md text-[#FAF9F6] transition-all"
                                />
                            </div>

                            <div className="flex gap-2.5 mt-2.5">
                                <button 
                                    type="button" 
                                    onClick={() => setModalOpen(false)} 
                                    className="btn-minimal-secondary w-full"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    disabled={isSubmitting} 
                                    className="btn-minimal w-full"
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
