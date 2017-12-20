<?php

namespace App\Http\Controllers;

use App\Barang;
use App\DetailPenjualanPos;
use App\PenjualanPos;
use App\TbsPenjualan;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function pilihPelanggan()
    {
        $pelanggan = User::where('tipe_user', 3)->get();
        $array     = array();
        foreach ($pelanggan as $pelanggans) {
            array_push($array, [
                'id'             => $pelanggans->id,
                'nama_pelanggan' => $pelanggans->name]);
        }

        return response()->json($array);
    }

    public function paginationData($penjualan, $array, $url)
    {

        //DATA PAGINATION
        $respons['current_page']   = $penjualan->currentPage();
        $respons['data']           = $array;
        $respons['first_page_url'] = url($url . '?page=' . $penjualan->firstItem());
        $respons['from']           = 1;
        $respons['last_page']      = $penjualan->lastPage();
        $respons['last_page_url']  = url($url . '?page=' . $penjualan->lastPage());
        $respons['next_page_url']  = $penjualan->nextPageUrl();
        $respons['path']           = url($url);
        $respons['per_page']       = $penjualan->perPage();
        $respons['prev_page_url']  = $penjualan->previousPageUrl();
        $respons['to']             = $penjualan->perPage();
        $respons['total']          = $penjualan->total();
        //DATA PAGINATION

        return $respons;
    }
    public function paginationPencarianData($penjualan, $array, $url, $search)
    {
        //DATA PAGINATION
        $respons['current_page']   = $penjualan->currentPage();
        $respons['data']           = $array;
        $respons['first_page_url'] = url($url . '?page=' . $penjualan->firstItem() . '&search=' . $search);
        $respons['from']           = 1;
        $respons['last_page']      = $penjualan->lastPage();
        $respons['last_page_url']  = url($url . '?page=' . $penjualan->lastPage() . '&search=' . $search);
        $respons['next_page_url']  = $penjualan->nextPageUrl();
        $respons['path']           = url($url);
        $respons['per_page']       = $penjualan->perPage();
        $respons['prev_page_url']  = $penjualan->previousPageUrl();
        $respons['to']             = $penjualan->perPage();
        $respons['total']          = $penjualan->total();
        //DATA PAGINATION

        return $respons;
    }

    public function viewTbsPenjualan()
    {
        $session_id    = session()->getId();
        $user_warung   = Auth::user()->id_warung;
        $tbs_penjualan = TbsPenjualan::with(['produk'])->where('warung_id', $user_warung)->where('session_id', $session_id)->orderBy('id_tbs_penjualan', 'desc')->paginate(10);
        $array         = array();

        foreach ($tbs_penjualan as $tbs_penjualans) {
            $potongan_persen = ($tbs_penjualans->potongan / ($tbs_penjualans->jumlah_produk * $tbs_penjualans->harga_produk)) * 100;

            if ($tbs_penjualans->potongan > 0) {
                $potongan = number_format($tbs_penjualans->potongan, 0, ',', '.') . " (" . round($potongan_persen, 2) . "%)";
            } else {
                $potongan = $tbs_penjualans->potongan;
            }

            array_push($array, [
                'id_tbs_penjualan' => $tbs_penjualans->id_tbs_penjualan,
                'nama_produk'      => $tbs_penjualans->NamaProduk,
                'kode_produk'      => $tbs_penjualans->produk->kode_barang,
                'jumlah_produk'    => $tbs_penjualans->jumlah_produk,
                'harga_produk'     => $tbs_penjualans->harga_produk,
                'potongan'         => $potongan,
                'subtotal'         => $tbs_penjualans->subtotal,
                'produk'           => $tbs_penjualans->id_produk . "|" . $tbs_penjualans->NamaProduk . "|" . $tbs_penjualans->harga_jual]);
        }

        $url     = '/penjualan/view-tbs-penjualan';
        $respons = $this->paginationData($tbs_penjualan, $array, $url);

        return response()->json($respons);
    }

    public function pencarianTbsPenjualan(Request $request)
    {
        $session_id    = session()->getId();
        $user_warung   = Auth::user()->id_warung;
        $tbs_penjualan = TbsPenjualan::select('tbs_penjualans.id_tbs_penjualan AS id_tbs_penjualan', 'tbs_penjualans.jumlah_produk AS jumlah_produk', 'barangs.nama_barang AS nama_barang', 'barangs.kode_barang AS kode_barang', 'tbs_penjualans.id_produk AS id_produk', 'tbs_penjualans.potongan AS potongan', 'tbs_penjualans.subtotal AS subtotal', 'tbs_penjualans.harga_produk AS harga_produk')
            ->leftJoin('barangs', 'barangs.id', '=', 'tbs_penjualans.id_produk')
            ->where('warung_id', $user_warung)->where('session_id', $session_id)
            ->where(function ($query) use ($request) {

                $query->orWhere('barangs.kode_barang', 'LIKE', $request->search . '%')
                    ->orWhere('barangs.nama_barang', 'LIKE', $request->search . '%');

            })->orderBy('tbs_penjualans.id_tbs_penjualan', 'desc')->paginate(10);

        $array = array();
        foreach ($tbs_penjualan as $tbs_penjualans) {
            $potongan_persen = ($tbs_penjualans['potongan'] / ($tbs_penjualans['jumlah_produk'] * $tbs_penjualans['harga_produk'])) * 100;

            if ($tbs_penjualans['potongan'] > 0) {
                $potongan = number_format($tbs_penjualans['potongan']) . " (" . round($potongan_persen, 2) . "%)";
            } else {
                $potongan = $tbs_penjualans['potongan'];
            }

            array_push($array, [
                'id_tbs_penjualan' => $tbs_penjualans['id_tbs_penjualan'],
                'nama_produk'      => title_case($tbs_penjualans['nama_barang']),
                'kode_produk'      => $tbs_penjualans['kode_barang'],
                'jumlah_produk'    => $tbs_penjualans['jumlah_produk'],
                'potongan'         => $potongan,
                'harga_produk'     => $tbs_penjualans['harga_produk'],
                'subtotal'         => $tbs_penjualans['subtotal'],
                'produk'           => $tbs_penjualans['id_produk'] . "|" . title_case($tbs_penjualans['nama_barang']) . "|" . $tbs_penjualans->harga_jual]);
        }

        $url    = '/penjualan/pencarian-tbs-penjualan';
        $search = $request->search;

        $respons = $this->paginationPencarianData($tbs_penjualan, $array, $url, $search);

        return response()->json($respons);
    }

    public function prosesTambahTbsPenjualan(Request $request)
    {
        $produk     = explode("|", $request->produk);
        $id_produk  = $produk[0];
        $harga_jual = $produk[3];
        $satuan_id  = $produk[4];
        $session_id = session()->getId();

        $data_tbs = TbsPenjualan::where('id_produk', $id_produk)
            ->where('session_id', $session_id)->where('warung_id', Auth::user()->id_warung)
            ->count();

//JIKA PRODUK YG DIPILIH SUDAH ADA DI TBS
        if ($data_tbs > 0) {

            return 0;

        } else {

            $subtotal     = $request->jumlah_produk * $harga_jual;
            $tbspenjualan = TbsPenjualan::create([
                'session_id'    => $session_id,
                'satuan_id'     => $satuan_id,
                'id_produk'     => $id_produk,
                'jumlah_produk' => $request->jumlah_produk,
                'harga_produk'  => $harga_jual,
                'subtotal'      => $subtotal,
                'warung_id'     => Auth::user()->id_warung,
            ]);

            $respons['subtotal'] = $subtotal;

            return response()->json($respons);
        }
    }

//PROSE EDIT JUMLAH TBS PENJUALAN
    public function prosesEditJumlahTbsPenjualan(Request $request)
    {

        $tbs_penjualan = TbsPenjualan::find($request->id_tbs);

        $subtotal = ($tbs_penjualan->harga_produk * $request->jumlah_produk) - $tbs_penjualan->potongan;

        $tbs_penjualan->update(['jumlah_produk' => $request->jumlah_produk, 'subtotal' => $subtotal]);

        $respons['subtotal'] = $subtotal;

        return response()->json($respons);
    }

    public function prosesEditPotonganTbsPenjualan(Request $request)
    {
        $tbs_penjualan = TbsPenjualan::find($request->id_tbs);

        $total = $tbs_penjualan->jumlah_produk * $tbs_penjualan->harga_produk;

        $potongan_produk = $this->cekPotongan($request->potongan_produk, $tbs_penjualan->harga_produk, $tbs_penjualan->jumlah_produk);

        if ($potongan_produk == '') {

            $respons['status'] = 0;

            return response()->json($respons);

        } else if ($potongan_produk > $total) {

            $respons['status'] = 1;

            return response()->json($respons);

        } else {

            $subtotal = ($tbs_penjualan->jumlah_produk * $tbs_penjualan->harga_produk) - $potongan_produk;

            $tbs_penjualan->update(['potongan' => $potongan_produk, 'subtotal' => $subtotal]);

            $respons['subtotal'] = $subtotal;

            return response()->json($respons);
        }

    }

    public function prosesHapusTbsPenjualan($id)
    {

        if (!TbsPenjualan::destroy($id)) {
            return 0;
        } else {
            return response(200);
        }

    }

    public function cekPotongan($potongan, $harga_produk, $jumlah_produk)
    {
        $cek_potongan = substr_count($potongan, '%'); // UNTUK CEK APAKAH ADA STRING "%" atau maksudnya untuk cek apakah pot. dalam bentuk persen atau tidak

        // JIKA POTONGAN TIDAK DALAM BENTUK PERSEN
        if ($cek_potongan == 0) {

            // FILTER POTONGAN, SEMUA BENTUK STRING AKAN DI DIFILTER KECUALI FLOAT/KOMA
            $potongan_produk = filter_var($potongan, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        } else {
            // JIKA POTONGAN DALAM BENTUK PERSEN
            // FILTER POTONGAN, SEMUA BENTUK STRING AKAN DI DIFILTER KECUALI FLOAT/KOMA
            $potongan_persen = filter_var($potongan, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            // UBAH NILAI POTONGAN KE BENTUK DESIMAL
            $potongan_produk = ($harga_produk * $jumlah_produk) * $potongan_persen / 100;
        }

        return $potongan_produk;
    }

    public function cekDataTbsPenjualan()
    {

        $session_id    = session()->getId();
        $tbs_penjualan = TbsPenjualan::select([DB::raw('SUM(subtotal) as subtotal')])->where('session_id', $session_id);

        if ($tbs_penjualan->first()->subtotal == null or $tbs_penjualan->first()->subtotal == '') {
            $subtotal = 0;
        } else {
            $subtotal = $tbs_penjualan->first()->subtotal;
        }

        $respons['subtotal'] = $subtotal;

        return response()->json($respons);
    }

    public function queryProduk($id_produk)
    {

        $barang = Barang::find($id_produk);

        return $barang;

    }

    public function index()
    {
        //
    }

/**
 * Show the form for creating a new resource.
 *
 * @return \Illuminate\Http\Response
 */
    public function create()
    {
        //
    }

/**
 * Store a newly created resource in storage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
    public function store(Request $request)
    {
        //START TRANSAKSI
        DB::beginTransaction();
        $warung_id  = Auth::user()->id_warung;
        $session_id = session()->getId();
        $user       = Auth::user()->id;
        $no_faktur  = PenjualanPos::no_faktur($warung_id);
        //INSERT DETAIL PENJUALAN
        $data_produk_penjualan = TbsPenjualan::with('produk')->where('session_id', $session_id)->where('warung_id', Auth::user()->id_warung);

        if ($data_produk_penjualan->count() == 0) {

            return $data_produk_penjualan->count();

        } else {

            //INSERT PENJUALAN

            $penjualan = PenjualanPos::create([
                'no_faktur'        => $no_faktur,
                'total'            => $request->total_akhir,
                'pelanggan_id'     => $request->pelanggan,
                'status_penjualan' => 'Tunai',
                'potongan'         => $request->potongan,
                'tunai'            => $request->pembayaran,
                'kembalian'        => $request->kembalian,
                'kredit'           => $request->kredit,
                'nilai_kredit'     => $request->kredit,
                'id_kas'           => $request->kas,
                'status_jual_awal' => 'Tunai',
                'warung_id'        => Auth::user()->id_warung,
            ]);

            foreach ($data_produk_penjualan->get() as $data_tbs) {

                $detail_penjualan = new DetailPenjualanPos();
                $stok_produk      = $detail_penjualan->stok_produk($data_tbs->id_produk);
                $sisa             = $stok_produk - $data_tbs->jumlah_produk;

                if ($sisa < 0) {
                    //DI BATALKAN PROSES NYA

                    $respons['respons']     = 1;
                    $respons['nama_produk'] = title_case($data_tbs->produk->nama_barang);
                    $respons['stok_produk'] = $stok_produk;
                    DB::rollBack();
                    return response()->json($respons);

                } else {

                    $detail_penjualan = DetailPenjualanPos::create([
                        'id_penjualan_pos' => $penjualan->id,
                        'no_faktur'        => $no_faktur,
                        'satuan_id'        => $data_tbs->satuan_id,
                        'id_produk'        => $data_tbs->id_produk,
                        'jumlah_produk'    => $data_tbs->jumlah_produk,
                        'harga_produk'     => $data_tbs->harga_produk,
                        'subtotal'         => $data_tbs->subtotal,
                        'potongan'         => $data_tbs->potongan,
                        'warung_id'        => Auth::user()->id_warung,
                    ]);

                }
            }

            //HAPUS TBS PENJUALAN
            $data_produk_penjualan->delete();
            DB::commit();
            return response(200);

        }
    }

/**
 * Display the specified resource.
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
    public function show($id)
    {
        //
    }

/**
 * Show the form for editing the specified resource.
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
    public function edit($id)
    {
        //
    }

/**
 * Update the specified resource in storage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
    public function update(Request $request, $id)
    {
        //
    }

/**
 * Remove the specified resource from storage.
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
    public function destroy($id)
    {
        //
    }
}