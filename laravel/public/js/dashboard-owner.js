
    'use strict';

    const API = '/api';

    const money = value =>
        new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(Number(value) || 0);

    const date = value => {
        if (!value) return '-';
        const d = new Date(value);
        return Number.isNaN(d.getTime())
            ? '-'
            : d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
    };

    function getToken() {
        for (const key of [
            'token', 'authToken', 'access_token',
            'accessToken', 'jwt', 'jwt_token'
        ]) {
            const value = localStorage.getItem(key);
            if (value && value !== 'undefined' && value !== 'null') {
                return value;
            }
        }
        return null;
    }

    async function api(path) {
        const token = getToken();
        const response = await fetch(API + path, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {})
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(
                `${path}: HTTP ${response.status}. Periksa token dan izin akun Owner.`
            );
        }

        const result = await response.json();

        if (result.success === false) {
            throw new Error(result.message || `Gagal mengambil ${path}`);
        }

        return result;
    }

    function dataOf(result) {
        return result?.data ?? result ?? {};
    }

    function rowsOf(result) {
        const data = dataOf(result);
        if (Array.isArray(data)) return data;
        if (Array.isArray(data?.data)) return data.data;
        return [];
    }

    function setText(selector, value) {
        const el = document.querySelector(selector);
        if (el) el.textContent = value;
    }

    function setCardValue(viewName, index, value) {
        const view = document.querySelector(`[data-view="${viewName}"]`);
        if (!view) return;

        const cards = view.querySelectorAll('.card.stat .val');
        if (cards[index - 1]) {
            cards[index - 1].textContent = value;
        }
    }

    function renderTable(viewName, tableIndex, rows, renderRow, colCount) {
        const view = document.querySelector(`[data-view="${viewName}"]`);
        if (!view) return;

        const tables = view.querySelectorAll('table');
        const table = tables[tableIndex];
        const tbody = table?.querySelector('tbody');
        if (!tbody) return;

        tbody.replaceChildren();

        if (!rows.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = colCount;
            td.textContent = 'Belum ada data.';
            td.style.textAlign = 'center';
            td.style.padding = '20px';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        rows.forEach(row => {
            const tr = document.createElement('tr');
            renderRow(row).forEach(value => {
                const td = document.createElement('td');
                td.textContent = value == null || value === '' ? '-' : String(value);
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
    }

    function showError(message) {
        console.error('[Owner Dashboard]', message);

        let notice = document.getElementById('owner-api-notice');
        if (!notice) {
            notice = document.createElement('div');
            notice.id = 'owner-api-notice';
            notice.style.cssText =
                'margin:12px 0;padding:12px 16px;border-radius:8px;' +
                'background:#fff1f2;color:#9f1239;font-size:13px';

            const main = document.querySelector('main') ||
                document.querySelector('.main-content') ||
                document.querySelector('.content') ||
                document.body;

            main.prepend(notice);
        }

        notice.textContent = 'Data gagal dimuat: ' + message;
    }

    async function loadOwnerDashboard() {
        const dashboard = document.querySelector('[data-view="dashboard"]');
        if (!dashboard) {
            console.error('Elemen [data-view="dashboard"] tidak ditemukan.');
            return;
        }

        try {
            const [incomeResponse, expenseResponse, profitResponse, transactionResponse] =
                await Promise.all([
                    api('/laporan/pendapatan'),
                    api('/laporan/biaya'),
                    api('/laporan/keuntungan'),
                    api('/transaksi')
                ]);

            const income = dataOf(incomeResponse);
            const expense = dataOf(expenseResponse);
            const profit = dataOf(profitResponse);
            const transactions = rowsOf(transactionResponse);

            const totalIncome = Number(income.total_pendapatan) || 0;
            const totalExpense = Number(expense.total_biaya) || 0;
            const totalProfit = Number(
                profit.keuntungan ?? totalIncome - totalExpense
            );

            // Simpan data asli dari backend.
            window.ownerDashboardData = {
                pendapatan: income,
                biaya: expense,
                keuntungan: profit,
                transaksi: transactions
            };

            // Dashboard: 4 kartu ringkasan.
            setCardValue('dashboard', 1, money(totalIncome));
            setCardValue('dashboard', 2, money(totalIncome));
            setCardValue('dashboard', 3, money(totalExpense));
            setCardValue('dashboard', 4, money(totalProfit));

            // Pendapatan.
            setCardValue('pendapatan', 1, money(totalIncome));
            setCardValue('pendapatan', 2, money(totalIncome));

            // Keuntungan.
            const profitView = document.querySelector('[data-view="keuntungan"]');
            if (profitView) {
                const values = profitView.querySelectorAll('.card .val');
                if (values[0]) values[0].textContent = money(totalIncome);
                if (values[1]) values[1].textContent = money(totalExpense);
                if (values[2]) values[2].textContent = money(totalProfit);
            }

            // Transaksi terbaru dari backend.
            const sortedTransactions = [...transactions].sort(
                (a, b) =>
                    new Date(b.created_at || 0) -
                    new Date(a.created_at || 0)
            );

            renderTable(
                'riwayat',
                0,
                sortedTransactions,
                t => [
                    t.kode_transaksi,
                    t.pelanggan?.nama_pelanggan || t.pelanggan?.nama,
                    date(t.created_at),
                    money(t.total_harga),
                    t.status_pembayaran,
                    t.status_pengambilan
                ],
                6
            );

            // Biaya operasional dari backend.
            const expenseRows = rowsOf(expenseResponse);
            renderTable(
                'biaya',
                0,
                expenseRows,
                e => [
                    date(e.tanggal_biaya),
                    e.nama_biaya || e.keterangan,
                    e.kategori,
                    money(e.jumlah_biaya),
                    e.pencatat?.nama || e.pencatat?.username
                ],
                5
            );

            // Aktivitas terbaru berdasarkan transaksi asli.
            const activities = dashboard.querySelectorAll('.act');
            sortedTransactions.slice(0, 3).forEach((t, i) => {
                const item = activities[i];
                if (!item) return;
                setTextWithin(item, 'b', `Transaksi ${t.kode_transaksi || '-'}`);
                setTextWithin(item, 'span', money(t.total_harga));
                setTextWithin(item, 'time', date(t.created_at));
            });

            const note = document.querySelector('[data-view="riwayat"] .note');
            if (note) {
                note.textContent =
                    `Menampilkan ${sortedTransactions.length} transaksi dari backend.`;
            }

            document.getElementById('owner-api-notice')?.remove();
            console.info('[Owner Dashboard] Data berhasil dimuat.');
        } catch (error) {
            showError(error.message);
        }
    }

    function setTextWithin(parent, selector, value) {
        const element = parent.querySelector(selector);
        if (element) element.textContent = value;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadOwnerDashboard);
    } else {
        loadOwnerDashboard();
    }

    window.refreshOwnerDashboard = loadOwnerDashboard;

