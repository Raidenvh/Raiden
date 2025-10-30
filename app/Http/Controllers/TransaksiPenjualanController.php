<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPenjualan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TransaksiPenjualanController extends Controller
{
    /**
     * VIEW: Menampilkan semua transaksi.
     */
    public function index()
    {
        $transaksis = TransaksiPenjualan::with('details.product')->latest()->paginate(10);
        
        // Mengirim data ke view
        return view('transaksi.index', compact('transaksis'));
    }

    /**
     * CREATE (FORM): Menampilkan form untuk membuat transaksi baru.
     */
    public function create()
    {
        $products = Product::orderBy('title')->get();
        return view('transaksi.create', compact('products'));
    }

    /**
     * CREATE (ACTION): Menyimpan tranccsaksi baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input (sudah baik)
        $request->validate([
            'nama_kasir' => 'required|string|max:50',
            'email_pembeli' => 'nullable|email',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.jumlah' => 'required|integer|min:1',
        ]);

        $grandTotal = 0;
        $itemsToProcess = [];

        foreach ($request->products as $productData) {
            $product = Product::find($productData['id']);

            // Jika stok tidak mencukupi, langsung gagalkan proses
            if ($product->stock < $productData['jumlah']) {
                return redirect()->back()
                    ->with('error', 'Stok untuk produk "' . $product->title . '" tidak mencukupi. Sisa stok: ' . $product->stock)
                    ->withInput();
            }

            $subtotal = $product->price * $productData['jumlah'];
            $grandTotal += $subtotal;

            // Siapkan data untuk disimpan nanti
            $itemsToProcess[] = [
                'product' => $product,
                'jumlah' => $productData['jumlah'],
                'harga_saat_transaksi' => $product->price, // Kunci harga saat ini
                'subtotal' => $subtotal,
            ];
        }
             $transaksi = DB::transaction(function () use ($request, $grandTotal, $itemsToProcess) {

            $transaksi = TransaksiPenjualan::create([
                'nama_kasir' => $request->nama_kasir,
                'email_pembeli' => $request->email_pembeli,
                'total_harga' => $grandTotal,
            ]);

            foreach ($itemsToProcess as $item) {
                $transaksi->details()->create([
                    'id_product' => $item['product']->id,
                    'jumlah_pembelian' => $item['jumlah'],
                    'harga_saat_transaksi' => $item['harga_saat_transaksi'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->decrement('stock', $item['jumlah']);
            }

            return $transaksi;
        });

     

        try {
            DB::transaction(function () use ($request, $grandTotal, $itemsToProcess) {
                // Simpan data transaksi utama dengan total harga
                $transaksi = TransaksiPenjualan::create([
                    'nama_kasir' => $request->nama_kasir,
                    'email_pembeli' => $request->email_pembeli,
                    'total_harga' => $grandTotal, // Simpan total harga
                ]);

                // Simpan detail transaksi dan kurangi stok
                foreach ($itemsToProcess as $item) {
                    $transaksi->details()->create([
                        'id_product' => $item['product']->id,
                        'jumlah_pembelian' => $item['jumlah'],
                        'harga_saat_transaksi' => $item['harga_saat_transaksi'], // Simpan harga
                        'subtotal' => $item['subtotal'], // Simpan subtotal
                    ]);

                    // Kurangi stok produk dengan metode yang lebih aman
                    $item['product']->decrement('stock', $item['jumlah']);
                }
            });
            $this->sendEmail($request->email_pembeli, $transaksi->id);

            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dibuat.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat transaksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show
     * 
     * 
     */
    public function show(TransaksiPenjualan $transaksi)
    {
        // Eager load relasi untuk efisiensi
        $transaksi->load('details.product');
        return view('transaksi.show', compact('transaksi'));
    }
    
    /**
     * UPDATE (FORM): Menampilkan form untuk mengedit transaksi.
     */
    public function edit(TransaksiPenjualan $transaksi)
    {
        $products = Product::orderBy('title')->get();
        // Load relasi detail untuk digunakan di form
        $transaksi->load('details');
        
        return view('transaksi.edit', compact('transaksi', 'products'));
    }

    /**
     * UPDATE (ACTION): Memperbarui data transaksi di database.
     */
    public function update(Request $request, TransaksiPenjualan $transaksi)
    {        
        $request->validate([
            'nama_kasir' => 'required|string|max:50',
            'email_pembeli' => 'nullable|email',
        ]);

        $transaksi->update($request->only(['nama_kasir', 'email_pembeli']));
        $this->sendEmail($request->email_pembeli, $transaksi->id);  
        return redirect()->route('transaksi.index')->with('success', 'Data kasir berhasil diupdate.');
    }

    /**
     * DELETE: Menghapus transaksi dari database.
     */
    public function destroy(TransaksiPenjualan $transaksi)
    {
        DB::transaction(function() use ($transaksi) {
            foreach($transaksi->details as $detail) {
                Product::find($detail->id_product)->increment('stock', $detail->jumlah_pembelian);
            }
            $transaksi->delete();
        });
        
        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
    }
public function sendEmail($to, $id)
{
    // Get transaksi by ID
    $transaksi_penjualan = new \App\Models\TransaksiPenjualan;
    $transaksi = $transaksi_penjualan
        ->where("transaksi_penjualan.id", $id)
        ->first();

   $total_harga['transaksi'] = 0;
if ($transaksi->details) {
    foreach ($transaksi->details as $detail) {
        $total_harga['transaksi'] += ($detail->product->price ?? 0) * ($detail->jumlah_pembelian ?? 0);
    
}

    }

    $transaksi_ = [
        'transaksi' => $transaksi,
        'total_harga' => $total_harga
    ];

    // Mengirim email
    Mail::send('sendmail.show', $transaksi_, function ($message) use ($to, $transaksi, $total_harga) {
        $message->to($to)
            ->subject("Transaksi Details: " . ($transaksi->first()->email_pembeli ?? 'Tidak Ada Email') . " - dengan Total tagihan RP " 
            . number_format($total_harga['transaksi'], 2, ',', '.') . ".");
    });

    return response()->json(['message' => 'Email sent successfully!']);
}
}