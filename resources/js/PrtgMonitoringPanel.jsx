import React, { useState } from "react";

const cleanHtml = (str) => {
    if (!str) return "";
    return String(str).replace(/<[^>]*>/g, "").trim();
};

export default function PrtgMonitoringPanel({ prtgCustomers = [], getDeviceStatus, getDeviceDuration }) {
    const [activeTab, setActiveTab] = useState("all");
    const [localSearch, setLocalSearch] = useState("");

    // Counts
    const counts = {
        all: prtgCustomers.length,
        online: prtgCustomers.filter(d => getDeviceStatus(d) === "online").length,
        down: prtgCustomers.filter(d => getDeviceStatus(d) === "down").length,
        warning: prtgCustomers.filter(d => getDeviceStatus(d) === "warning").length,
        paused: prtgCustomers.filter(d => getDeviceStatus(d) === "paused").length
    };

    // Filtered list
    const filteredList = prtgCustomers.filter(d => {
        const matchesTab = activeTab === "all" || getDeviceStatus(d) === activeTab;
        const deviceName = d && d.device ? String(d.device) : "";
        const messageText = d && d.message ? String(d.message) : "";
        const matchesSearch = deviceName.toLowerCase().includes(localSearch.toLowerCase()) || 
                              messageText.toLowerCase().includes(localSearch.toLowerCase());
        return matchesTab && matchesSearch;
    });

    return (
        <section className="app-card p-6 rounded-md mb-6 border-l-4 border-l-[#6366F1]">
            <style>{`
                @keyframes pulseOrange {
                    0% { transform: scale(0.85); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
                    70% { transform: scale(1.1); box-shadow: 0 0 0 5px rgba(245, 158, 11, 0); }
                    100% { transform: scale(0.85); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
                }
                .pulse-orange {
                    animation: pulseOrange 1.5s infinite !important;
                }
            `}</style>

            {/* Header */}
            <div className="flex justify-between items-center mb-5 pb-3 border-b border-[#E4E4E7] flex-wrap gap-3">
                <div className="flex items-center gap-2.5">
                    <h3 className="m-0 text-lg font-bold text-[#111111]">
                        Monitoring Status Device (PRTG)
                    </h3>
                    <div className="flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#ECFDF5] border border-[#A7F3D0] text-[10px] text-[#047857] font-bold uppercase tracking-wider">
                        <span className="w-1.5 h-1.5 rounded-full bg-[#059669] pulse-green"></span>
                        <span>Live</span>
                    </div>
                </div>
                <span className="bg-[#F4F4F5] border border-[#E4E4E7] text-[#111111] px-3 py-1.5 rounded-md text-xs font-bold font-mono">
                    {counts.all} Total Device
                </span>
            </div>

            <p className="m-0 mb-5 text-[#71717A] text-sm">
                Status real-time pelanggan yang terhubung ke jaringan. Data diperbarui otomatis setiap 10 detik.
            </p>

            {/* Controls Row */}
            <div className="flex justify-between items-center gap-4 flex-wrap mb-5">
                {/* Tabs */}
                <div className="flex gap-1 bg-[#F4F4F5] p-1 rounded-md border border-[#E4E4E7] flex-wrap">
                    <TabButton active={activeTab === "all"} onClick={() => setActiveTab("all")} label="Semua" count={counts.all} />
                    <TabButton active={activeTab === "online"} onClick={() => setActiveTab("online")} label="Online" count={counts.online} />
                    <TabButton active={activeTab === "down"} onClick={() => setActiveTab("down")} label="Down" count={counts.down} />
                    <TabButton active={activeTab === "warning"} onClick={() => setActiveTab("warning")} label="Warning" count={counts.warning} />
                    <TabButton active={activeTab === "paused"} onClick={() => setActiveTab("paused")} label="Paused" count={counts.paused} />
                </div>

                {/* Local Search */}
                <input 
                    type="text"
                    placeholder="Cari nama device/pesan..."
                    value={localSearch}
                    onChange={(e) => setLocalSearch(e.target.value)}
                    className="px-3 py-1.5 rounded-md border border-[#E4E4E7] text-xs bg-[#FFFFFF] text-[#111111] focus:border-[#6366F1]/40 focus:ring-0 w-64 transition-all"
                />
            </div>

            {/* Device Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[600px] overflow-y-auto pr-1">
                {filteredList.length === 0 ? (
                    <div className="col-span-full text-center p-10 text-[#71717A] text-sm app-card border-dashed">
                        Tidak ada device yang cocok dengan kriteria filter/pencarian.
                    </div>
                ) : (
                    filteredList.map((d, index) => {
                        const status = getDeviceStatus(d);
                        const duration = getDeviceDuration(d);
                        
                        // Theme styling berdasarkan status
                        const theme = {
                            online: { 
                                cardClass: "border-[#A7F3D0] hover:border-[#10B981]/50 bg-[#ECFDF5]/60", 
                                badgeClass: "bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0]" 
                            },
                            down: { 
                                cardClass: "border-[#FCA5A5] hover:border-[#EF4444]/50 bg-[#FEF2F2]/60", 
                                badgeClass: "bg-[#FEF2F2] text-[#B91C1C] border border-[#FCA5A5]", 
                                pulse: "red" 
                            },
                            warning: { 
                                cardClass: "border-[#FDE68A] hover:border-[#F59E0B]/50 bg-[#FFFBEB]/60", 
                                badgeClass: "bg-[#FFFBEB] text-[#B45309] border border-[#FDE68A]", 
                                pulse: "orange" 
                            },
                            paused: { 
                                cardClass: "border-[#E4E4E7] bg-[#FFFFFF] hover:border-[#8B5CF6]/40", 
                                badgeClass: "bg-[#F4F4F5] text-[#71717A] border border-[#E4E4E7]" 
                            },
                            unknown: { 
                                cardClass: "border-[#E4E4E7] bg-[#FFFFFF] hover:border-[#8B5CF6]/40", 
                                badgeClass: "bg-[#F4F4F5] text-[#71717A] border border-[#E4E4E7]" 
                            }
                        }[status] || { 
                            cardClass: "border-[#E4E4E7] bg-[#FFFFFF] hover:border-[#8B5CF6]/40", 
                            badgeClass: "bg-[#F4F4F5] text-[#71717A] border border-[#E4E4E7]" 
                        };
                        
                        return (
                            <div key={index} className={`app-card p-5 flex flex-col justify-between border ${theme.cardClass}`}>
                                <div className="flex justify-between items-start mb-3 gap-2">
                                    <div className="font-bold text-[#111111] text-sm flex items-center gap-1.5">
                                        <span>{d.device}</span>
                                    </div>
                                    <span className={`text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider flex items-center ${theme.badgeClass}`}>
                                        {theme.pulse && (
                                            <span className={`w-1.5 h-1.5 rounded-full inline-block mr-1.5 ${
                                                theme.pulse === 'red' ? 'bg-[#EF4444] pulse-green' : 'bg-[#F59E0B] pulse-orange'
                                            }`}></span>
                                        )}
                                        {status.toUpperCase()}
                                    </span>
                                </div>

                                <div className="text-xs text-[#71717A] mb-2">
                                    <strong>Durasi:</strong> <span className="text-[#111111] font-semibold">{duration}</span>
                                </div>

                                {(d.lastup || d.lastdown) && (
                                    <div className="text-[10px] text-[#71717A]/80 mb-3 font-mono">
                                        {status === 'online' ? `Terakhir offline: ${cleanHtml(d.lastdown) || "-"}` : `Terakhir online: ${cleanHtml(d.lastup) || "-"}`}
                                    </div>
                                )}

                                <div className="bg-[#F4F4F5] p-3 rounded border border-[#E4E4E7] text-xs text-[#111111] min-h-[42px] flex items-center word-break break-all font-mono leading-relaxed">
                                    {cleanHtml(d.message) || "Tidak ada rincian pesan."}
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </section>
    );
}

function TabButton({ active, onClick, label, count }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`px-3.5 py-1.5 rounded-md text-xs font-bold transition-all cursor-pointer flex items-center gap-2 ${
                active 
                    ? "bg-[#111111] text-[#FFFFFF]" 
                    : "text-[#71717A] hover:bg-[#E4E4E7]/60 hover:text-[#111111]"
            }`}
        >
            {label}
            <span className={`text-[10px] px-1.5 py-0.5 rounded-full font-mono font-bold ${
                active ? "bg-[#FFFFFF]/20 text-[#FFFFFF]" : "bg-[#E4E4E7] text-[#71717A]"
            }`}>
                {count}
            </span>
        </button>
    );
}
