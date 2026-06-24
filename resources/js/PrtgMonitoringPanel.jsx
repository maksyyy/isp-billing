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
        <section className="app-card p-6 rounded-md mb-6 border-l-4 border-l-[#FAF9F6]">
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
            <div className="flex justify-between items-center mb-5 pb-3 border-b border-[#222226] flex-wrap gap-3">
                <div className="flex items-center gap-2.5">
                    <h3 className="m-0 text-lg font-bold text-[#FAF9F6]">
                        Monitoring Status Device (PRTG)
                    </h3>
                    <div className="flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#0C2D1F]/50 border border-[#10B981]/20 text-[10px] text-[#10B981] font-bold uppercase tracking-wider">
                        <span className="w-1.5 h-1.5 rounded-full bg-[#10B981] pulse-green"></span>
                        <span>Live</span>
                    </div>
                </div>
                <span className="bg-[#0B0B0D] border border-[#222226] text-[#FAF9F6] px-3 py-1.5 rounded-md text-xs font-bold font-mono">
                    {counts.all} Total Device
                </span>
            </div>

            <p className="m-0 mb-5 text-[#8E8E90] text-sm">
                Status real-time pelanggan yang terhubung ke jaringan. Data diperbarui otomatis setiap 10 detik.
            </p>

            {/* Controls Row */}
            <div className="flex justify-between items-center gap-4 flex-wrap mb-5">
                {/* Tabs */}
                <div className="flex gap-1 bg-[#0B0B0D] p-1 rounded-md border border-[#222226] flex-wrap">
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
                    className="px-3 py-1.5 rounded-md border border-[#222226] text-xs bg-[#0B0B0D] text-[#FAF9F6] focus:border-[#FAF9F6]/40 focus:ring-0 w-64 transition-all"
                />
            </div>

            {/* Device Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[600px] overflow-y-auto pr-1">
                {filteredList.length === 0 ? (
                    <div className="col-span-full text-center p-10 text-[#8E8E90] text-sm app-card border-dashed">
                        Tidak ada device yang cocok dengan kriteria filter/pencarian.
                    </div>
                ) : (
                    filteredList.map((d, index) => {
                        const status = getDeviceStatus(d);
                        const duration = getDeviceDuration(d);
                        
                        // Theme styling berdasarkan status
                        const theme = {
                            online: { 
                                cardClass: "border-[#10B981]/25 hover:border-[#10B981]/50 bg-[#0C2D1F]/10", 
                                badgeClass: "bg-[#0C2D1F]/50 text-[#10B981] border border-[#10B981]/20" 
                            },
                            down: { 
                                cardClass: "border-[#EF4444]/25 hover:border-[#EF4444]/50 bg-[#2F1517]/10", 
                                badgeClass: "bg-[#2F1517]/50 text-[#EF4444] border border-[#EF4444]/20", 
                                pulse: "red" 
                            },
                            warning: { 
                                cardClass: "border-[#F59E0B]/25 hover:border-[#F59E0B]/50 bg-[#2E200C]/10", 
                                badgeClass: "bg-[#2E200C]/50 text-[#F59E0B] border border-[#F59E0B]/20", 
                                pulse: "orange" 
                            },
                            paused: { 
                                cardClass: "border-[#222226] bg-[#0C0C0E]/40", 
                                badgeClass: "bg-[#0B0B0D] text-[#8E8E90] border border-[#222226]" 
                            },
                            unknown: { 
                                cardClass: "border-[#222226] bg-[#0C0C0E]/40", 
                                badgeClass: "bg-[#0B0B0D] text-[#8E8E90] border border-[#222226]" 
                            }
                        }[status] || { 
                            cardClass: "border-[#222226] bg-[#0C0C0E]/40", 
                            badgeClass: "bg-[#0B0B0D] text-[#8E8E90] border border-[#222226]" 
                        };
                        
                        return (
                            <div key={index} className={`app-card p-5 flex flex-col justify-between border ${theme.cardClass}`}>
                                <div className="flex justify-between items-start mb-3 gap-2">
                                    <div className="font-bold text-[#FAF9F6] text-sm flex items-center gap-1.5">
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

                                <div className="text-xs text-[#8E8E90] mb-2">
                                    <strong>Durasi:</strong> <span className="text-[#FAF9F6] font-semibold">{duration}</span>
                                </div>

                                {(d.lastup || d.lastdown) && (
                                    <div className="text-[10px] text-[#8E8E90]/70 mb-3 font-mono">
                                        {status === 'online' ? `Terakhir offline: ${cleanHtml(d.lastdown) || "-"}` : `Terakhir online: ${cleanHtml(d.lastup) || "-"}`}
                                    </div>
                                )}

                                <div className="bg-[#0B0B0D] p-3 rounded border border-[#222226] text-xs text-[#FAF9F6] min-h-[42px] flex items-center word-break break-all font-mono leading-relaxed">
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
                    ? "bg-[#FAF9F6] text-[#0C0C0D]" 
                    : "text-[#8E8E90] hover:bg-[#121216] hover:text-[#FAF9F6]"
            }`}
        >
            {label}
            <span className={`text-[10px] px-1.5 py-0.5 rounded-full font-mono font-bold ${
                active ? "bg-[#0C0C0D]/10 text-[#0C0C0D]" : "bg-[#1E1E20] text-[#8E8E90]"
            }`}>
                {count}
            </span>
        </button>
    );
}
