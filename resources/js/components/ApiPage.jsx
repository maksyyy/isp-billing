import { useEffect, useState } from "react";
import axios from "axios";

export default function ApiPage() {
    const [data, setData] = useState({
        total_customers: 0,
        total_invoices: 0,
        unpaid_invoices: 0,
        total_income: 0
    });

    useEffect(() => {
        axios.get("/isp-billing/public/api-page")
            .then(res => setData(res.data))
            .catch(err => console.error(err));
    }, []);

    return (
        <div className="p-6 grid grid-cols-4 gap-4">

            <Card title="Total Pelanggan" value={data.total_customers} />
            <Card title="Total Invoice" value={data.total_invoices} />
            <Card title="Belum Bayar" value={data.unpaid_invoices} />
            <Card title="Total Pemasukan" value={"Rp " + data.total_income} />

        </div>
    );
}

function Card({ title, value }) {
    return (
        <div className="bg-white shadow rounded p-4">
            <h3 className="text-gray-500">{title}</h3>
            <p className="text-2xl font-bold">{value}</p>
        </div>
    );
}