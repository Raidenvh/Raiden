<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class TransaksiPenjualan extends Model
{
    use HasFactory;

    /**
     * Menentukan nama tabel secara eksplisit.
     */
    protected $table = 'transaksi_penjualan';

    public function get_transaksi_penjualan() {
    $sql = $this->select('*');
    
    return $sql;
    }

    public function get_all_transaksi_penjualan() {
        $sql = $this->select("transaksi_penjualan.*", "transaksi_penjualan_detail*","products.title","products.price",
        "category_product.product_category_name as product_category_name",
        DB::raw("SUM(transaksi_penjualan_detail.jumlah) as total_harga"))
        ->join("transaksi_penjualan_detail", "transaksi_penjualan_detail.id_transaksi", "=","transaksi_penjualan.id")
        ->join("products","transaksi_penjualan_detail.id_product", "=","products.id")
        ->join("category_product","category_product.id", "=","products.product_category_id");
                    
        return $sql;
    }

    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     */
    protected $fillable = [
        'nama_kasir',
        'email_pembeli',
        'tanggal_transaksi',
    ];

    /**
     * Mendefinisikan relasi "satu ke banyak" (one-to-many) ke DetailTransaksiPenjualan.
     * Satu transaksi bisa memiliki banyak detail produk.
     */
    public function details()
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'id_transaksi_penjualan');
    }
}