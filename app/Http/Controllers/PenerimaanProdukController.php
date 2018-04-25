<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PembelianOrder;
use App\DetailPembelianOrder;
use App\TbsPenerimaanProduk;
use Illuminate\Support\Facades\DB;
use Auth;

class PenerimaanProdukController extends Controller
{

    // DATA PAGINTION
	public function dataPagination($tbs_penerimaan_produks, $array, $url)
	{

        //DATA PAGINATION
		$respons['current_page']   = $tbs_penerimaan_produks->currentPage();
		$respons['data']           = $array;
		$respons['first_page_url'] = url($url . '?page=' . $tbs_penerimaan_produks->firstItem());
		$respons['from']           = 1;
		$respons['last_page']      = $tbs_penerimaan_produks->lastPage();
		$respons['last_page_url']  = url($url . '?page=' . $tbs_penerimaan_produks->lastPage());
		$respons['next_page_url']  = $tbs_penerimaan_produks->nextPageUrl();
		$respons['path']           = url($url);
		$respons['per_page']       = $tbs_penerimaan_produks->perPage();
		$respons['prev_page_url']  = $tbs_penerimaan_produks->previousPageUrl();
		$respons['to']             = $tbs_penerimaan_produks->perPage();
		$respons['total']          = $tbs_penerimaan_produks->total();
        //DATA PAGINATION

		return $respons;
	}

    // DATA SUPLIER ORDER
	public function suplierOrder(){

		$data_order = PembelianOrder::select(['pembelian_orders.id', 'pembelian_orders.no_faktur_order', 'pembelian_orders.suplier_id', 'pembelian_orders.keterangan','supliers.nama_suplier'])
		->leftJoin('supliers', 'supliers.id', '=', 'pembelian_orders.suplier_id')
		->where('pembelian_orders.status_order', 1)
		->where('pembelian_orders.warung_id', Auth::user()->id_warung)->get();

		$array = [];

		foreach ($data_order as $order) {
			array_push($array, [
				'id_order'		=> $order->id,
				'suplier_id'	=> $order->suplier_id,
				'faktur_order'	=> $order->no_faktur_order,
				'suplier_order'	=> $order->nama_suplier,
				'order'			=> $order->id."|".$order->suplier_id."|".$order->no_faktur_order."|".$order->nama_suplier."|".$order->keterangan
				]);
		}

		return response()->json($array);

	}


    // VIEW TBS PENERIMAAN PRODUK
	public function viewTbsPenerimaanProduk()
	{
		$session_id  = session()->getId();
		$user_warung = Auth::user()->id_warung;

		$tbs_penerimaan_produks = TbsPenerimaanProduk::dataTbsPenerimaanProduk($session_id, $user_warung)
		->orderBy('tbs_penerimaan_produks.id_tbs_penerimaan_produk', 'desc')->paginate(10);

		$array = [];
		foreach ($tbs_penerimaan_produks as $tbs_penerimaan_produk) {

			array_push($array, [
				'data_tbs'			=> $tbs_penerimaan_produk,
				'nama_satuan'       => strtoupper($tbs_penerimaan_produk->nama_satuan),
				]);
		}

		$url     = '/penerimaan-produk/view-tbs-penerimaan-produk';
		$respons = $this->dataPagination($tbs_penerimaan_produks, $array, $url);

		return response()->json($respons);
	}


    // PENCARIAN TBS PENERIMAAN PRODUK
	public function pencarianTbsPenerimaanProduk(Request $request)
	{
		$session_id  = session()->getId();
		$user_warung = Auth::user()->id_warung;

		$tbs_penerimaan_produks = TbsPenerimaanProduk::dataTbsPenerimaanProduk($session_id, $user_warung)
		->where(function ($query) use ($request) {

			$query->orWhere('barangs.nama_barang', 'LIKE', '%'. $request->search . '%')
			->orWhere('barangs.kode_barang', 'LIKE', '%'. $request->search . '%');

		})->orderBy('tbs_penerimaan_produks.id_tbs_penerimaan_produk', 'desc')->paginate(10);

		$array = [];
		foreach ($tbs_penerimaan_produks as $tbs_penerimaan_produk) {

			array_push($array, [
				'data_tbs'			=> $tbs_penerimaan_produk,
				'nama_satuan'       => strtoupper($tbs_penerimaan_produk->nama_satuan),
				]);
		}

		$url     = '/penerimaan-produk/view-tbs-penerimaan-produk';
		$respons = $this->dataPagination($tbs_penerimaan_produks, $array, $url);

		return response()->json($respons);
	}


	// GET PEMEBLIAN ORDER - PENERIMAAN ORDER
	public function prosesTbsPenerimaanProduk(Request $request){

		if (Auth::user()->id_warung == '') {
			Auth::logout();
			return response()->view('error.403');
		}else{

			$data_orders = DetailPembelianOrder::where('no_faktur_order', $request->faktur_order)
			->where('warung_id', Auth::user()->id_warung)->get();

			$session_id = session()->getId();
			$subtotal = 0;

			// HAPUS DATA TBS SUPLIER LAMA, JIKA TIBA TIBA SUPLIER DIUBAH
			$hapus_tbs = TbsPenerimaanProduk::where('session_id', $session_id)->where('warung_id', Auth::user()->id_warung)->delete();

			foreach ($data_orders as $data_order) {
				
				$insert_tbs = TbsPenerimaanProduk::create([
					'session_id'     	=> $session_id,
					'no_faktur_order'	=> $data_order->no_faktur_order,
					'id_produk'   		=> $data_order->id_produk,
					'jumlah_produk'		=> $data_order->jumlah_produk,
					'satuan_id' 		=> $data_order->satuan_id,
					'satuan_dasar'     	=> $data_order->satuan_dasar,
					'harga_produk'    	=> $data_order->harga_produk,
					'subtotal' 			=> $data_order->subtotal,
					'tax' 				=> $data_order->tax,
					'potongan'    		=> $data_order->potongan,
					'status_harga'		=> $data_order->status_harga,
					'warung_id'			=> $data_order->warung_id
					]);

				$subtotal = $subtotal + $data_order->subtotal;
			}

			$respons['status']   = 0;
			$respons['subtotal'] = $subtotal;

			return response()->json($respons);
		}

	}


    //PROSES BATAL TBS PENERIMAAN PRODUK
	public function batalPenerimaanProduk()
	{

		if (Auth::user()->id_warung == '') {
			Auth::logout();
			return response()->view('error.403');
		} else {
			$session_id         = session()->getId();
			$data_tbs_pembelian = TbsPenerimaanProduk::where('session_id', $session_id)->where('warung_id', Auth::user()->id_warung)->delete();

			return response(200);
		}
	}
}
