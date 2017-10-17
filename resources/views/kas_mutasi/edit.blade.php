@extends('layouts.app') 
 
@section('content') 
 
    <div class="row"> 
      <div class="col-md-12"> 
        <ul class="breadcrumb"> 
          <li><a href="{{ url('/home') }}">Home</a></li> 
          <li><a href="{{ url('/kas_mutasi') }}">Kas Mutasi</a></li> 
          <li class="active">Edit Kas Mutasi</li> 
        </ul> 
 
     <div class="card"> 
              <div class="card-header card-header-icon" data-background-color="purple"> 
                       <i class="material-icons">compare_arrows</i> 
                                </div> 
                      <div class="card-content"> 
                         <h4 class="card-title"> Kas Mutasi </h4> 
                       
            {!! Form::model($kas_mutasi, ['url' => route('kas_mutasi.update', $kas_mutasi->id), 'method' => 'put', 'files'=>'true','class'=>'form-horizontal']) !!} 
              @include('kas_mutasi._form') 
            {!! Form::close() !!} 
          </div> 
        </div> 
      </div> 
    </div> 
@endsection 
 
 
@section('scripts') 
<script type="text/javascript"> 
 
    $(document).ready(function(){ 
 
      var kas = $("#dari_kas").val();         
      var ke_kas = $("#ke_kas").val(); 
 
 
        $.post('{{ route('cek_total_kas') }}',{'_token': $('meta[name=csrf-token]').attr('content'),kas:kas}, function(data){ 
          $("#sisa_kas").val(data); 
          }); 
       
      $(document).on('change','#dari_kas', function(){ 
 
        var kas = $(this).val();         
        var ke_kas = $("#ke_kas").val(); 
 
          if (kas == ke_kas) { 
 
            alert("Dari Kas dan Ke Kas Tidak Bisa Sama!"); 
            document.getElementById('ke_kas').selectize.setValue(''); 
 
          }else{ 
 
            $.post('{{ route('cek_total_kas') }}',{'_token': $('meta[name=csrf-token]').attr('content'),kas:kas}, function(data){ 
            $("#sisa_kas").val(data); 
 
          }); 
        } 
             
      }) 
 
      $(document).on('change','#ke_kas', function(){ 
 
        var ke_kas = $(this).val(); 
        var dari_kas = $('#dari_kas').val(); 
 
        if (dari_kas == ke_kas) { 
 
          alert("Dari Kas dan Ke Kas Tidak Bisa Sama!"); 
          document.getElementById('ke_kas').selectize.setValue(''); 
        } 
 
         
             
      }) 
 
 
      $(document).on('click','#submit_kas', function(){ 
 
        var jumlah = $(this).val(); 
        var sisa_kas = $('#sisa_kas').val();         
        var jumlah_lama = "{{ $kas_mutasi->jumlah }}"; 
 
        if (sisa_kas == '') { 
          sisa_kas = 0; 
        } 
 
        var hitung_kas = (parseInt(sisa_kas,10) + parseInt(jumlah_lama,10)) - parseInt(jumlah,10); 
 
        if (hitung_kas < 0) { 
          alert("Total Kas Tidak Mencukupi!"); 
          $(this).val(''); 
        } 
             
      }) 
 
     
    }); 
             
 
</script> 
@endsection 