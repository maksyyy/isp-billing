import React, { useState } from "react";
import { Line } from "react-chartjs-2";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from "chart.js";

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

export default function MikrotikMonitoringPanel({
    data,
    loading,
    // Traffic props dari BackboneAlerts
    interfaces = [],
    selectedInterface,
    onSelectInterface,
    trafficHistory = { times: [], rx: [], tx: [] },
}) {
    const [activeSection, setActiveSection] = useState("traffic"); // default ke traffic
    const [searchTerm, setSearchTerm] = useState("");

    if (loading && !data) {
        return (
            <div className="app-card p-6 rounded-md mb-6 border-l-4 border-l-[#6366F1]">
                <h3 className="m-0 text-lg font-bold text-[#111111]">Router MikroTik Monitor</h3>
                <p className="text-[#71717A] text-sm mt-2">Menghubungi router MikroTik...</p>
            </div>
        );
    }

    if (!data || !data.connected) {
        return (
            <div className="app-card p-6 rounded-md mb-6 border-l-4 border-l-[#DC2626]">
                <h3 className="m-0 text-lg font-bold text-[#111111] flex items-center gap-2">
                    Router MikroTik Monitor 
                    <span className="text-[9px] uppercase font-bold px-2 py-0.5 rounded-full bg-[#FEF2F2] text-[#B91C1C] border border-[#FCA5A5] tracking-wider">
                        Disconnected
                    </span>
                </h3>
                <p className="text-[#71717A] text-sm mt-3">
                    {data?.message || "Koneksi ke MikroTik belum dikonfigurasi. Silakan masuk ke Pengaturan Sistem untuk menghubungkan router."}
                </p>
                <a 
                    href="/settings?tab=mikrotik" 
                    className="btn-minimal px-4 py-2 text-xs font-semibold inline-block mt-3"
                >
                    Konfigurasi MikroTik
                </a>
            </div>
        );
    }

    const { resources, active_users, address_lists } = data;

    // Filter active users
    const filteredActive = (active_users || []).filter(u =>
        u.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.service.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Filter address lists
    const filteredAddresses = (address_lists || []).filter(entry =>
        entry.customer_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.customer_code.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.list.toLowerCase().includes(searchTerm.toLowerCase()) ||
        entry.comment.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // RAM calculation
    const ramFreeMb = Math.round((resources?.free_memory || 0) / 1048576);
    const ramTotalMb = Math.round((resources?.total_memory || 0) / 1048576);
    const ramUsedMb = ramTotalMb - ramFreeMb;
    const ramUsedPercent = ramTotalMb > 0 ? Math.round((ramUsedMb / ramTotalMb) * 100) : 0;

    // Traffic helpers
    const currentIface = selectedInterface || interfaces[0] || null;
    const currentRxSpeed = trafficHistory.rx.length > 0 ? trafficHistory.rx[trafficHistory.rx.length - 1] : 0;
    const currentTxSpeed = trafficHistory.tx.length > 0 ? trafficHistory.tx[trafficHistory.tx.length - 1] : 0;

    const formatSpeed = (mbps) => {
        if (!mbps) return "0.00 Mbps";
        if (mbps >= 1000) return `${(mbps / 1000).toFixed(2)} Gbps`;
        return `${mbps.toFixed(2)} Mbps`;
    };

    // Chart.js config
    const chartData = {
        labels: trafficHistory.times,
        datasets: [
            {
                label: "Download (RX)",
                data: trafficHistory.rx,
                borderColor: "#6366F1",
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                    gradient.addColorStop(0, "rgba(99, 102, 241, 0.15)");
                    gradient.addColorStop(1, "rgba(99, 102, 241, 0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: (ctx) => ctx.dataIndex === ctx.dataset.data.length - 1 ? 5 : 0,
                pointBackgroundColor: "#6366F1",
                pointBorderColor: "#FFFFFF",
                pointBorderWidth: 2,
            },
            {
                label: "Upload (TX)",
                data: trafficHistory.tx,
                borderColor: "#8B5CF6",
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                    gradient.addColorStop(0, "rgba(139, 92, 246, 0.10)");
                    gradient.addColorStop(1, "rgba(139, 92, 246, 0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 1.5,
                pointRadius: (ctx) => ctx.dataIndex === ctx.dataset.data.length - 1 ? 4 : 0,
                pointBackgroundColor: "#8B5CF6",
                pointBorderColor: "#FFFFFF",
                pointBorderWidth: 1.5,
            }
        ]
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "top",
                labels: { color: "#111111", font: { size: 11, weight: "bold" } }
            },
            tooltip: {
                backgroundColor: "#FFFFFF",
                borderColor: "#E4E4E7",
                borderWidth: 1,
                titleColor: "#111111",
                bodyColor: "#71717A",
                callbacks: {
                    label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw.toFixed(2)} Mbps`
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: "#71717A", font: { size: 10 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: "rgba(0, 0, 0, 0.05)" },
                ticks: {
                    color: "#71717A",
                    font: { size: 10 },
                    callback: (v) => `${v} M`
                }
            }
        }
    };

    return (
        <section className="app-card p-6 rounded-md mb-6 border-l-4 border-l-[#6366F1]">
            {/* Header */}
            <div className="flex justify-between items-center mb-5 pb-3 border-b border-[#E4E4E7] flex-wrap gap-3">
                <div className="flex items-center gap-2.5">
                    <h3 className="m-0 text-lg font-bold text-[#111111]">
                        Router MikroTik Monitor
                    </h3>
                    <div className="flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#ECFDF5] border border-[#A7F3D0] text-[10px] text-[#047857] font-bold uppercase tracking-wider">
                        <span className="w-1.5 h-1.5 rounded-full bg-[#059669] pulse-green"></span>
                        <span>Connected</span>
                    </div>
                </div>
                <span className="bg-[#F4F4F5] border border-[#E4E4E7] text-[#111111] px-3 py-1.5 rounded-md text-xs font-bold font-mono">
                    {resources?.board_name || "Device"}
                </span>
            </div>

            <p className="m-0 mb-5 text-[#71717A] text-sm">
                Status real-time, spesifikasi beban kerja CPU, RAM, serta pemetaan firewall address-list pelanggan MikroTik.
            </p>

            {/* Navigation Tabs */}
            <div className="flex justify-between items-center gap-4 flex-wrap mb-5">
                <div className="flex gap-1 bg-[#F4F4F5] p-1 rounded-md border border-[#E4E4E7] flex-wrap">
                    {[
                        { key: "traffic", label: "Traffic" },
                        { key: "resources", label: "Resources" },
                        { key: "active_users", label: `Active Users (${(active_users || []).length})` },
                        { key: "address_lists", label: `Address List (${(address_lists || []).length})` },
                    ].map(({ key, label }) => (
                        <button
                            key={key}
                            type="button"
                            onClick={() => setActiveSection(key)}
                            className={`px-3 py-1.5 rounded-md text-xs font-bold transition-all cursor-pointer ${
                                activeSection === key 
                                    ? "bg-[#111111] text-[#FFFFFF]" 
                                    : "text-[#71717A] hover:bg-[#E4E4E7]/60 hover:text-[#111111]"
                            }`}
                        >
                            {label}
                        </button>
                    ))}
                </div>

                {activeSection !== "resources" && activeSection !== "traffic" && (
                    <input
                        type="text"
                        placeholder="Cari..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="px-3 py-1.5 rounded-md border border-[#E4E4E7] text-xs bg-[#FFFFFF] text-[#111111] focus:border-[#6366F1]/40 focus:ring-0 w-56 transition-all"
                    />
                )}
            </div>

            {/* ===== TAB: TRAFFIC ===== */}
            {activeSection === "traffic" && (
                <div>
                    {/* Interface selector + speed strip */}
                    <div className="flex justify-between items-center flex-wrap gap-3.5 p-4 bg-[#F4F4F5]/60 rounded-md border border-[#E4E4E7] mb-4">
                        <div className="flex items-center gap-3 flex-wrap">
                            {/* Dropdown interface */}
                            {interfaces.length > 0 ? (
                                <select
                                    value={currentIface?.name || ""}
                                    onChange={(e) => {
                                        const match = interfaces.find(i => i.name === e.target.value);
                                        if (match && onSelectInterface) onSelectInterface(match);
                                    }}
                                    className="px-3 py-1.5 rounded-md border border-[#E4E4E7] text-xs font-semibold bg-[#FFFFFF] text-[#111111] focus:border-[#6366F1]/40 focus:ring-0 cursor-pointer min-w-[200px]"
                                >
                                    {interfaces.map(i => (
                                        <option key={i.id || i.name} value={i.name}>
                                            {i.name} — {i.type.toUpperCase()} {i.running ? "(Running)" : "(Inactive)"}
                                        </option>
                                    ))}
                                </select>
                            ) : (
                                <span className="text-xs text-[#71717A]">Mendeteksi interface...</span>
                            )}

                            {/* Uptime badge */}
                            {resources?.uptime && (
                                <span className="text-xs text-[#111111] bg-[#FFFFFF] px-3 py-1.5 rounded-md border border-[#E4E4E7] font-semibold">
                                    Uptime: {resources.uptime}
                                </span>
                            )}
                        </div>

                        {/* Live RX/TX speeds */}
                        <div className="flex gap-5 items-center flex-wrap">
                            <div className="flex flex-col text-right">
                                <span className="text-[10px] text-[#71717A] font-bold uppercase tracking-wider">Download RX</span>
                                <strong className="text-base text-[#6366F1] font-mono">{formatSpeed(currentRxSpeed)}</strong>
                            </div>
                            <div className="flex flex-col text-right">
                                <span className="text-[10px] text-[#71717A] font-bold uppercase tracking-wider">Upload TX</span>
                                <strong className="text-base text-[#8B5CF6] font-mono">{formatSpeed(currentTxSpeed)}</strong>
                            </div>
                            <div className="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#ECFDF5] border border-[#A7F3D0] text-[9px] text-[#047857] font-bold uppercase tracking-wider">
                                <span className="w-1.5 h-1.5 rounded-full bg-[#059669] pulse-green"></span>
                                <span>Live</span>
                            </div>
                        </div>
                    </div>

                    {/* Chart */}
                    <div className="h-[260px] mt-4 relative">
                        {trafficHistory.times.length === 0 ? (
                            <div className="flex flex-col items-center justify-center h-full gap-2 text-[#71717A] text-xs bg-[#F4F4F5]/60 border border-[#E4E4E7] rounded-md border-dashed p-6">
                                <span className="font-semibold">Menunggu polling data traffic pertama...</span>
                                <small className="text-[#71717A]">Data akan muncul dalam ~5 detik setelah interface dipilih</small>
                            </div>
                        ) : (
                            <Line data={chartData} options={chartOptions} />
                        )}
                    </div>
                </div>
            )}

            {/* ===== TAB: RESOURCES ===== */}
            {activeSection === "resources" && resources && (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mt-2.5">
                    {/* CPU */}
                    <div className="app-card p-5 flex flex-col justify-between min-h-[160px]">
                        <span className="text-[10px] text-[#71717A] font-bold uppercase tracking-wider">CPU Load</span>
                        <div className="relative w-20 h-20 flex items-center justify-center mx-auto my-2">
                            <svg width="80" height="80" viewBox="0 0 36 36" className="transform -rotate-90">
                                <path stroke="#E4E4E7" strokeWidth="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path stroke="#6366F1" strokeWidth="3.5" strokeDasharray={`${resources.cpu_load || 0}, 100`} fill="none" strokeLinecap="round" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span className="absolute text-lg font-bold text-[#111111] font-mono">{resources.cpu_load || 0}%</span>
                        </div>
                    </div>

                    {/* RAM */}
                    <div className="app-card p-5 flex flex-col justify-between min-h-[160px]">
                        <span className="text-[10px] text-[#71717A] font-bold uppercase tracking-wider">Memory / RAM Usage</span>
                        <div className="flex justify-between text-xs font-semibold text-[#111111] mb-1.5">
                            <span>Terpakai: <span className="font-mono">{ramUsedMb} MB</span></span>
                            <span className="text-[#71717A]">Total: <span className="font-mono">{ramTotalMb} MB</span></span>
                        </div>
                        <div className="h-2.5 bg-[#E4E4E7] rounded-full border border-[#E4E4E7] overflow-hidden">
                            <div className="h-full bg-[#111111] rounded-full" style={{ width: `${ramUsedPercent}%` }}></div>
                        </div>
                        <span className="text-[10px] text-[#71717A] mt-2 block">Bebas: <span className="font-mono">{ramFreeMb} MB</span> ({100 - ramUsedPercent}%)</span>
                    </div>

                    {/* System Info */}
                    <div className="app-card p-5 flex flex-col justify-between min-h-[160px]">
                        <span className="text-[10px] text-[#71717A] font-bold uppercase tracking-wider">System Details</span>
                        <div className="text-xs text-[#111111] flex flex-col gap-2">
                            {[
                                ["Uptime", resources.uptime],
                                ["Version", `v${resources.version}`],
                                ["CPU Freq", `${resources.cpu_frequency} MHz`],
                            ].map(([k, v]) => (
                                <div key={k} className="flex justify-between border-b border-[#E4E4E7] pb-1 font-semibold">
                                    <span className="text-[#71717A]">{k}:</span>
                                    <span>{v || "Unknown"}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            )}

            {/* ===== TAB: ACTIVE USERS ===== */}
            {activeSection === "active_users" && (
                <div className="mt-2.5">
                    {filteredActive.length === 0 ? (
                        <div className="p-10 text-center text-[#71717A] text-sm app-card border-dashed">
                            Tidak ada pengguna aktif yang ditemukan.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {filteredActive.map((user, i) => (
                                <div key={i} className="app-card p-5 flex flex-col justify-between transition-all hover:border-[#6366F1]/20">
                                    {/* Header card */}
                                    <div className="flex justify-between items-start mb-3.5 gap-2">
                                        <div>
                                            {user.customer_name ? (
                                                <>
                                                    <div className="font-bold text-sm text-[#111111]">
                                                        {user.customer_name}
                                                    </div>
                                                    <div className="text-[10px] text-[#71717A] font-semibold mt-1">
                                                        PPPoE: <code className="bg-[#F4F4F5] px-1.5 py-0.5 rounded border border-[#E4E4E7] text-[#111111] font-mono">{user.name}</code>
                                                    </div>
                                                </>
                                            ) : (
                                                <div className="font-bold text-sm text-[#111111]">
                                                    {user.name}
                                                    <span className="text-[10px] text-[#71717A] ml-2 font-normal">Unidentified</span>
                                                </div>
                                            )}
                                        </div>
                                        <div className="flex flex-col gap-1.5 items-end shrink-0">
                                            <span className="text-[9px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0]">
                                                {user.service}
                                            </span>
                                            {user.is_active !== null && (
                                                <span className={user.is_active ? "status-badge-active" : "status-badge-inactive"}>
                                                    {user.is_active ? "Aktif" : "Nonaktif"}
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Detail rows */}
                                    <div className="grid grid-cols-[80px_1fr] gap-x-3 gap-y-1.5 text-xs mb-3.5">
                                        <span className="text-[#71717A] font-semibold">IP Address</span>
                                        <span className="text-[#111111] font-mono font-semibold">{user.address}</span>

                                        {user.customer_code && user.customer_code !== '-' && (
                                            <>
                                                <span className="text-[#71717A] font-semibold">Kode</span>
                                                <span className="text-[#111111] font-bold">{user.customer_code}</span>
                                            </>
                                        )}

                                        {user.package_name && (
                                            <>
                                                <span className="text-[#71717A] font-semibold">Paket</span>
                                                <span className="text-[#111111] font-semibold">{user.package_name}</span>
                                            </>
                                        )}

                                        {user.customer_phone && (
                                            <>
                                                <span className="text-[#71717A] font-semibold">Telepon</span>
                                                <a href={`tel:${user.customer_phone}`} className="text-[#111111] underline hover:text-[#6366F1] font-medium">
                                                    {user.customer_phone}
                                                </a>
                                            </>
                                        )}

                                        <span className="text-[#71717A] font-semibold">Uptime</span>
                                        <span className="text-[#059669] font-bold font-mono">{user.uptime}</span>

                                        <span className="text-[#71717A] font-semibold">MAC / Caller</span>
                                        <span className="text-[#71717A] font-mono text-[10px] break-all">{user.caller_id}</span>
                                    </div>

                                    {/* Link ke profil pelanggan */}
                                    {user.customer_id && (
                                        <a
                                            href={`/customers/${user.customer_id}`}
                                            className="btn-minimal-secondary w-full text-center py-2 text-xs font-semibold"
                                        >
                                            Lihat Profil Pelanggan
                                        </a>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* ===== TAB: ADDRESS LIST ===== */}
            {activeSection === "address_lists" && (
                <div className="mt-2.5">
                    {filteredAddresses.length === 0 ? (
                        <div className="p-10 text-center text-[#71717A] text-sm app-card border-dashed">
                            Tidak ada data address list yang ditemukan.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {filteredAddresses.map((entry, i) => {
                                const isIsolated = entry.list?.toLowerCase().includes("isolir") ||
                                    entry.list?.toLowerCase().includes("block") ||
                                    entry.comment?.toLowerCase().includes("isolir");
                                const dbStatus = entry.is_active; // boolean from DB or null

                                return (
                                    <div key={i} className={`app-card p-5 flex flex-col justify-between border-l-4 transition-all hover:border-r-[#6366F1]/10 ${
                                        isIsolated ? 'border-l-[#DC2626]' : 'border-l-[#059669]'
                                    }`}>
                                        {/* Header */}
                                        <div className="flex justify-between items-start mb-3.5 gap-2">
                                            <div>
                                                <div className="font-bold text-sm text-[#111111]">
                                                    {entry.customer_name}
                                                </div>
                                                {entry.customer_code && entry.customer_code !== '-' && (
                                                    <div className="text-[10px] text-[#71717A] font-semibold mt-1">
                                                        Kode: <strong className="text-[#111111]">{entry.customer_code}</strong>
                                                    </div>
                                                )}
                                            </div>
                                            <div className="flex flex-col gap-1.5 items-end shrink-0">
                                                {/* Status MikroTik (address-list) */}
                                                <span className={isIsolated ? "status-badge-inactive" : "status-badge-active"}>
                                                    {isIsolated ? "TERISOLIR" : "AKTIF"}
                                                </span>
                                                {/* Status DB */}
                                                {dbStatus !== null && dbStatus !== undefined && (
                                                    <span className={dbStatus ? "status-badge-active" : "status-badge-inactive"}>
                                                        DB: {dbStatus ? "Aktif" : "Nonaktif"}
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        {/* Detail rows */}
                                        <div className="grid grid-cols-[80px_1fr] gap-x-3 gap-y-1.5 text-xs mb-3.5">
                                            <span className="text-[#71717A] font-semibold">IP Address</span>
                                            <span className="text-[#111111] font-mono font-semibold">{entry.address}</span>

                                            <span className="text-[#71717A] font-semibold">Address List</span>
                                            <span className="text-left">
                                                <span className={isIsolated ? "status-badge-inactive" : "status-badge-active"}>
                                                    {entry.list}
                                                </span>
                                            </span>

                                            {entry.package_name && entry.package_name !== '-' && (
                                                <>
                                                    <span className="text-[#71717A] font-semibold">Paket</span>
                                                    <span className="text-[#111111] font-semibold">{entry.package_name}</span>
                                                </>
                                            )}

                                            {entry.customer_phone && entry.customer_phone !== '-' && (
                                                <>
                                                    <span className="text-[#71717A] font-semibold">Telepon</span>
                                                    <a href={`tel:${entry.customer_phone}`} className="text-[#111111] underline hover:text-[#6366F1] font-medium">
                                                        {entry.customer_phone}
                                                    </a>
                                                </>
                                            )}

                                            {entry.comment && (
                                                <>
                                                    <span className="text-[#71717A] font-semibold">Comment</span>
                                                    <span className="text-[#71717A] text-[10px] break-all">{entry.comment}</span>
                                                </>
                                            )}
                                        </div>

                                        {/* Link profil */}
                                        {entry.customer_id && (
                                            <a
                                                href={`/customers/${entry.customer_id}`}
                                                className="btn-minimal-secondary w-full text-center py-2 text-xs font-semibold"
                                            >
                                                Lihat Profil Pelanggan
                                            </a>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            )}

        </section>
    );
}
