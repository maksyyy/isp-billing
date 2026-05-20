import React, { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";

function App({ role }) {
    const [data, setData] = useState(null);
    const [modal, setModal] = useState(null);
    const [prtg, setPrtg] = useState([]);
    const canViewFinance = ["admin", "finance"].includes(role);
    const canViewDevices = ["admin", "noc"].includes(role);
    const canViewPublicApi = ["admin", "finance"].includes(role);

    const [month, setMonth] = useState(
        new Date().toISOString().slice(0, 7)
    );

    useEffect(() => {
        const fetchData = () => {
            fetch(`/api/dashboard-data?month=${month}`)
                .then((res) => {
                    if (!res.ok) throw new Error("Gagal mengambil data dashboard");
                    return res.json();
                })
                .then((res) => setData(res))
                .catch((err) => console.error("Error fetchData:", err));
        };

        const fetchPrtg = () => {
            fetch(`/api/prtg`)
                .then((res) => {
                    if (!res.ok) throw new Error("Gagal mengambil data PRTG");
                    return res.json();
                })
                .then((res) => setPrtg(res.sensors || [])) // PERBAIKAN: ubah devices menjadi sensors
                .catch((err) => console.error("Error fetchPrtg:", err));
        };

        fetchData();
        if (canViewDevices) {
            fetchPrtg();
        }

        const interval = setInterval(() => {
            fetchData();
            if (canViewDevices) {
                fetchPrtg();
            }
        }, 10000);

        return () => clearInterval(interval);
    }, [month, canViewDevices]);

    if (!data) return <p style={{ padding: "20px" }}>⏳ Loading...</p>;

    // FILTER HANYA PELANGGAN (ID DIAWALI ANGKA)
    const isCustomer = (name) => /^\d+/.test(name);
    const filtered = prtg.filter((d) => isCustomer(d.device));

    // STATUS (PAKAI status_raw kalau ada)
    const online = filtered.filter((d) => d.status_raw == 3).length;

    const offline = filtered.filter(
        (d) =>
            [4, 5].includes(Number(d.status_raw)) ||
            d.status?.toLowerCase().includes("down") ||
            d.status?.toLowerCase().includes("warning")
    ).length;

    const paused = filtered.filter(
        (d) => d.status_raw == 7 || d.status?.toLowerCase().includes("paused")
    ).length;

    return (
        <div style={appContainer}>
            <style>{`
                body { margin: 0; background-color: #f8fafc; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
                * { box-sizing: border-box; }
                .modal-anim { animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
                @keyframes modalFadeIn {
                    from { opacity: 0; transform: translateY(20px) scale(0.95); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }
                ::-webkit-scrollbar { width: 8px; }
                ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
                ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
                ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
            `}</style>

            <div style={headerStyle}>
                <h2 style={titleStyle}>✨ API Dashboard</h2>

                {canViewFinance && (
                    <input
                        type="month"
                        value={month}
                        onChange={(e) => setMonth(e.target.value)}
                        style={inputStyle}
                    />
                )}
            </div>

            <div style={grid}>
                <Card
                    title="Total Pelanggan"
                    value={data.total_customers || 0}
                    color="#3b82f6"
                    onClick={() =>
                        setModal({
                            title: "Daftar Pelanggan",
                            type: "customers",
                            data: data.customers || [],
                            link: "/customers",
                        })
                    }
                />

                {canViewFinance && (
                    <>
                <Card
                    title="Total Invoice"
                    value={data.total_invoices || 0}
                    color="#6366f1"
                    onClick={() =>
                        setModal({
                            title: "Daftar Invoice",
                            type: "invoice",
                            data: data.invoice_list || [],
                            link: "/invoices",
                        })
                    }
                />

                <Card
                    title="Belum Dibayar"
                    value={data.unpaid_invoices || 0}
                    color="#ef4444"
                    onClick={() =>
                        setModal({
                            title: "Invoice Belum Dibayar",
                            type: "unpaid",
                            data: data.unpaid_list || [],
                            link: "/invoices",
                        })
                    }
                />

                <Card
                    title="Total Income"
                    value={"Rp " + (data.total_income || 0).toLocaleString("id-ID")}
                    color="#10b981"
                    onClick={() =>
                        setModal({
                            title: "Total Income",
                            type: "info",
                            data: [
                                "Total pemasukan bulan ini:",
                                "Rp " + (data.total_income || 0).toLocaleString("id-ID"),
                            ],
                        })
                    }
                />

                <Card
                    title="Total Tunggakan"
                    value={"Rp " + (data.unpaid_total_amount || 0).toLocaleString("id-ID")}
                    color="#f59e0b"
                    onClick={() =>
                        setModal({
                            title: "Detail Tunggakan",
                            type: "unpaid",
                            data: data.unpaid_list || [],
                            link: "/invoices",
                        })
                    }
                />
                    </>
                )}

                {/* PRTG */}
                {canViewDevices && (
                    <>
                <Card
                    title="Device Online"
                    value={`🟢 ${online}`}
                    color="#10b981"
                    onClick={() =>
                        setModal({
                            title: "Device Online",
                            type: "prtg",
                            data: filtered.filter((d) => d.status_raw == 3),
                        })
                    }
                />

                <Card
                    title="Device Offline"
                    value={`🔴 ${offline}`}
                    color="#ef4444"
                    onClick={() =>
                        setModal({
                            title: "Device Offline",
                            type: "prtg",
                            data: filtered.filter(
                                (d) =>
                                    [4, 5].includes(Number(d.status_raw)) ||
                                    d.status.toLowerCase().includes("down")
                            ),
                        })
                    }
                />

                <Card
                    title="Device Paused"
                    value={`🟡 ${paused}`}
                    color="#f59e0b"
                    onClick={() =>
                        setModal({
                            title: "Device Paused",
                            type: "prtg",
                            data: filtered.filter(
                                (d) =>
                                    d.status_raw == 7 ||
                                    d.status.toLowerCase().includes("paused")
                            ),
                        })
                    }
                />
                    </>
                )}
            </div>

            {canViewPublicApi && <PublicApiCustomers />}

            {/* MODAL */}
            {modal && (
                <div style={overlay} onClick={() => setModal(null)}>
                    <div style={modalBox} className="modal-anim" onClick={(e) => e.stopPropagation()}>
                        <h3>{modal.title}</h3>

                        <div style={content}>
                            {modal.type === "customers" &&
                                modal.data.map((item) => (
                                    <div key={item.id} style={listItem}>
                                        👤 {item.name}
                                    </div>
                                ))}

                            {modal.type === "invoice" &&
                                modal.data.map((item) => (
                                    <div key={item.id} style={listItem}>
                                        <div>
                                            👤 {item.customer?.name}
                                            <br />
                                            <small style={{ color: "#888" }}>
                                                {item.status}
                                            </small>
                                        </div>
                                        <div>
                                            Rp {Number(item.amount).toLocaleString("id-ID")}
                                        </div>
                                    </div>
                                ))}

                            {modal.type === "unpaid" &&
                                modal.data.map((item) => (
                                    <div key={item.id} style={listItem}>
                                        <span>👤 {item.customer?.name}</span>
                                        <span style={{ color: "red" }}>
                                            Rp {Number(item.amount).toLocaleString("id-ID")}
                                        </span>
                                    </div>
                                ))}

                            {modal.type === "info" &&
                                modal.data.map((text, i) => (
                                    <div key={i} style={listItem}>
                                        {text}
                                    </div>
                                ))}

                            {modal.type === "prtg" &&
                                modal.data.map((item, i) => (
                                    <div key={i} style={listItem}>
                                        <span>📡 {item.device}</span>
                                        <span
                                            style={{
                                                color:
                                                    item.status_raw == 3
                                                        ? "green"
                                                        : item.status_raw == 7
                                                        ? "orange"
                                                        : "red",
                                            }}
                                        >
                                            {item.status}
                                        </span>
                                    </div>
                                ))}
                        </div>

                        <div style={{ display: "flex", gap: "10px", marginTop: "15px" }}>
                            {modal.link && (
                                <a href={modal.link} style={btnBlue}>
                                    Lihat Semua
                                </a>
                            )}
                            <button style={btn} onClick={() => setModal(null)}>
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

function PublicApiCustomers() {
    const [customers, setCustomers] = useState([]);
    const [loadingCustomers, setLoadingCustomers] = useState(true);
    const [customersError, setCustomersError] = useState(null);

    useEffect(() => {
        setLoadingCustomers(true);
        setCustomersError(null);

        axios
            .get("https://jsonplaceholder.typicode.com/users")
            .then((res) => setCustomers(res.data))
            .catch(() => setCustomersError("Gagal mengambil data pelanggan dari Public API"))
            .finally(() => setLoadingCustomers(false));
    }, []);

    return (
        <section style={publicApiSection}>
            <div style={publicApiHeader}>
                <div>
                    <h3 style={{ margin: 0 }}>Data Pelanggan Public API</h3>
                    <p style={publicApiText}>
                        10 data pelanggan dummy dari JSONPlaceholder menggunakan Axios.
                    </p>
                </div>
                <span style={apiBadge}>Axios</span>
            </div>

            {loadingCustomers && (
                <div style={loadingBox}>
                    <strong>Loading data pelanggan...</strong>
                    <span>Mohon tunggu, data Public API sedang diambil.</span>
                </div>
            )}

            {customersError && <div style={errorBox}>{customersError}</div>}

            {!loadingCustomers && !customersError && (
                <div style={postsGrid}>
                    {customers.map((customer) => (
                        <article key={customer.id} style={postCard}>
                            <small style={{ color: "#64748b" }}>Customer #{customer.id}</small>
                            <h4 style={{ margin: "8px 0", color: "#111827" }}>
                                {customer.name}
                            </h4>
                            <p style={{ margin: "0 0 6px", color: "#4b5563" }}>
                                {customer.email}
                            </p>
                            <p style={{ margin: "0 0 6px", color: "#4b5563" }}>
                                {customer.phone}
                            </p>
                            <p style={{ margin: "0 0 6px", color: "#4b5563" }}>
                                {customer.address.street}, {customer.address.city}
                            </p>
                            <strong style={{ color: "#374151" }}>
                                {customer.company.name}
                            </strong>
                        </article>
                    ))}
                </div>
            )}
        </section>
    );
}

/* CARD */
function Card({ title, value, color, onClick }) {
    const [isHovered, setIsHovered] = useState(false);
    return (
        <div
            onClick={onClick}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
            style={{
                ...card,
                borderTop: `5px solid ${color}`,
                cursor: "pointer",
                transform: isHovered ? "translateY(-5px)" : "translateY(0)",
                boxShadow: isHovered
                    ? "0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04)"
                    : "0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)",
            }}
        >
            <h4 style={{ color: "#64748b", margin: "0 0 10px 0", fontSize: "14px", textTransform: "uppercase", letterSpacing: "0.5px" }}>{title}</h4>
            <p style={{ fontSize: "28px", fontWeight: "800", color, margin: 0 }}>{value}</p>
            <div style={{ marginTop: "15px", display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                <small style={{ color: "#94a3b8", fontWeight: "500" }}>Klik untuk detail &rarr;</small>
            </div>
        </div>
    );
}

/* STYLE */
const appContainer = {
    padding: "30px",
    maxWidth: "1200px",
    margin: "0 auto",
    color: "#334155"
};

const headerStyle = {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: "30px",
    paddingBottom: "15px",
    borderBottom: "1px solid #e2e8f0"
};

const titleStyle = {
    margin: 0,
    fontSize: "26px",
    fontWeight: "700",
    color: "#0f172a"
};

const inputStyle = {
    padding: "10px 15px",
    borderRadius: "8px",
    border: "1px solid #cbd5e1",
    fontSize: "15px",
    outline: "none",
    cursor: "pointer",
    boxShadow: "0 1px 3px rgba(0,0,0,0.05)",
    background: "#fff"
};

const grid = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))",
    gap: "24px",
};

const card = {
    background: "#fff",
    padding: "24px",
    borderRadius: "16px",
    transition: "all 0.3s ease",
};

const overlay = {
    position: "fixed",
    top: 0,
    left: 0,
    width: "100%",
    height: "100%",
    background: "rgba(15, 23, 42, 0.6)",
    backdropFilter: "blur(4px)",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    zIndex: 999
};

const modalBox = {
    background: "#fff",
    padding: "25px",
    borderRadius: "16px",
    width: "90%",
    maxWidth: "400px",
    boxShadow: "0 25px 50px -12px rgba(0,0,0,0.25)",
};

const content = {
    maxHeight: "350px",
    overflowY: "auto",
    marginTop: "20px",
    paddingRight: "5px"
};

const listItem = {
    padding: "12px 16px",
    marginBottom: "10px",
    background: "#f8fafc",
    borderRadius: "10px",
    border: "1px solid #e2e8f0",
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    fontSize: "14px",
    color: "#475569"
};

const btn = {
    padding: "10px 16px",
    background: "#ef4444",
    color: "#fff",
    border: "none",
    borderRadius: "8px",
    cursor: "pointer",
    fontWeight: "600",
    flex: 1,
    textAlign: "center"
};

const btnBlue = {
    ...btn,
    background: "#2563eb",
    textDecoration: "none",
};

const publicApiSection = {
    marginTop: "40px",
    background: "#fff",
    borderRadius: "16px",
    boxShadow: "0 10px 15px -3px rgba(0,0,0,0.05)",
    padding: "25px",
};

const publicApiHeader = {
    display: "flex",
    justifyContent: "space-between",
    gap: "15px",
    alignItems: "center",
    marginBottom: "15px",
};

const publicApiText = {
    margin: "6px 0 0",
    color: "#64748b",
};

const apiBadge = {
    background: "#dbeafe",
    color: "#1d4ed8",
    padding: "6px 10px",
    borderRadius: "999px",
    fontSize: "12px",
    fontWeight: "bold",
};

const loadingBox = {
    display: "flex",
    flexDirection: "column",
    gap: "6px",
    padding: "18px",
    border: "1px solid #bfdbfe",
    borderRadius: "8px",
    background: "#eff6ff",
    color: "#1e40af",
};

const errorBox = {
    padding: "14px",
    border: "1px solid #fecaca",
    borderRadius: "8px",
    background: "#fef2f2",
    color: "#b91c1c",
};

const postsGrid = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(260px, 1fr))",
    gap: "20px",
};

const postCard = {
    border: "1px solid #e5e7eb",
    borderRadius: "12px",
    padding: "20px",
    background: "#ffffff",
    boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.05)"
};

const container = document.getElementById("app");

if (container) {
    createRoot(container).render(<App role={container.dataset.role || ""} />);
}
