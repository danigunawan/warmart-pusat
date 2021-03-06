<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemaWarna extends Model
{
	public $fillable = ['nama_tema', 'kode_tema', 'header_tema', 'default_tema', 'warung_id'];

	public static function daftar($warung_id)
	{
		$tema 				= new TemaWarna();
		$tema->nama_tema    = "Default";
		$tema->kode_tema	= "#2AC326";
		$tema->header_tema	= "#E91E63";
		$tema->default_tema	= 0;
		$tema->warung_id 	= $warung_id;
		$tema->save();
		
		$tema 				= new TemaWarna();
		$tema->nama_tema    = "Meadowlark";
		$tema->kode_tema	= "#003e53";
		$tema->header_tema	= "#718a93";
		$tema->default_tema	= 1;
		$tema->warung_id 	= $warung_id;
		$tema->save();
		
		$tema 				= new TemaWarna();
		$tema->nama_tema    = "Cherry Tomato";
		$tema->kode_tema	= "#d71149";
		$tema->header_tema	= "#ffffff";
		$tema->default_tema	= 0;
		$tema->warung_id 	= $warung_id;
		$tema->save();
		
		$tema 				= new TemaWarna();
		$tema->nama_tema    = "Little Boy Blue";
		$tema->kode_tema	= "#00A591";
		$tema->header_tema	= "#ffffff";
		$tema->default_tema	= 0;
		$tema->warung_id 	= $warung_id;
		$tema->save();
		
		$tema 				= new TemaWarna();
		$tema->nama_tema    = "Chili Oil";
		$tema->kode_tema	= "#f74d18";
		$tema->header_tema	= "#ffffff";
		$tema->default_tema	= 0;
		$tema->warung_id 	= $warung_id;
		$tema->save();
	}
}
