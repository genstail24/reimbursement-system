<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Reimbursement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReimbursementSeeder extends Seeder
{
    public function run(): void
    {
        $employeeIds = User::role('employee')->pluck('id');
        $categories  = Category::all()->pluck('id', 'name');
        $now          = now();

        $data = [
            // --- PENDING ---
            [
                'title'          => 'Ojek Online ke Kantor Klien',
                'description'    => 'Perjalanan dengan Gojek pada tanggal 12 Juni 2025, dari kantor pusat (Kuningan) ke lokasi meeting klien di SCBD. Tarif dasar + surge pricing 1.5x. (Dummy data generated for example purposes.)',
                'amount'         => 78000,
                'status'         => 'pending',
                'category_id'    => $categories['Transportasi'],
                'submitted_at'   => $now->copy()->subDays(3),
            ],
            [
                'title'          => 'Obat & Vitamin untuk Flu',
                'description'    => 'Pembelian paket flu (Paracetamol 500mg x10 tablet, Vitamin C 1000mg x5 sachet) di Apotek Kimia Farma Tebet pada 14 Juni 2025. (Dummy data generated for example purposes.)',
                'amount'         => 95000,
                'status'         => 'pending',
                'category_id'    => $categories['Kesehatan'],
                'submitted_at'   => $now->copy()->subDays(2),
            ],

            // // --- APPROVED ---
            // [
            //     'title'          => 'Catering Makan Siang Tim',
            //     'description'    => 'Pesanan catering nasi box 20 porsi via Aplikasi TastyBox untuk meeting internal pada 10 Juni 2025, termasuk air mineral dan snack. (Dummy data generated for example purposes.)',
            //     'amount'         => 1200000,
            //     'status'         => 'approved',
            //     'category_id'    => $categories['Makan'],
            //     'submitted_at'   => $now->copy()->subDays(7),
            //     'approved_at'    => $now->copy()->subDays(5),
            // ],
            // [
            //     'title'          => 'Paket Data 30GB Bulanan',
            //     'description'    => 'Langganan paket data 30GB (24 jam) di Indosat Ooredoo melalui GraPARI pada 1 Juni 2025 untuk mendukung remote work. (Dummy data generated for example purposes.)',
            //     'amount'         => 175000,
            //     'status'         => 'approved',
            //     'category_id'    => $categories['Internet'],
            //     'submitted_at'   => $now->copy()->subDays(10),
            //     'approved_at'    => $now->copy()->subDays(8),
            // ],

            // // --- REJECTED ---
            // [
            //     'title'          => 'Booking Hotel Pribadi di Bandung',
            //     'description'    => 'Booking 1 malam di Hotel Lembang Asri untuk keperluan liburan pribadi pada 20 Mei 2025. (Dummy data generated for example purposes.)',
            //     'amount'         => 650000,
            //     'status'         => 'rejected',
            //     'category_id'    => $categories['Akomodasi'],
            //     'submitted_at'   => $now->copy()->subDays(20),
            //     'approved_at'    => $now->copy()->subDays(18),
            // ],
            // [
            //     'title'          => 'Keyboard Mechanical RGB',
            //     'description'    => 'Pembelian keyboard mechanical merk Keychron K2 via Tokopedia untuk penggunaan pribadi. (Dummy data generated for example purposes.)',
            //     'amount'         => 950000,
            //     'status'         => 'rejected',
            //     'category_id'    => $categories['Lainnya'],
            //     'submitted_at'   => $now->copy()->subDays(15),
            //     'approved_at'    => $now->copy()->subDays(14),
            // ],
        ];

        foreach ($data as $item) {
            $userId = $employeeIds->random();

            $reimbursement = Reimbursement::create([
                'user_id'         => $userId,
                'category_id'     => $item['category_id'],
                'title'           => $item['title'],
                'description'     => $item['description'],
                'amount'          => $item['amount'],
                'status'          => $item['status'],
                'submitted_at'    => $item['submitted_at'],
                'approved_at'     => $item['approved_at'] ?? null,
                'attachment_path' => 'https://images.vecteezy.com/system/resources/previews/000/436/021/non_2x/invoice-icon-png.png',
            ]);

            activity()
                ->performedOn($reimbursement)
                ->causedBy($userId)
                ->withProperties(['action' => 'submitted'])
                ->log('Reimbursement submission');
        }
    }
}
