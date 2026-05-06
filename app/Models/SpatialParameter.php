<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialParameter extends Model
{
	protected $table = 'spatial_parameters';

	protected $fillable = [
		'parameter_type',
		'parameter_name',
		'score',
		'geom',
	];

	protected $casts = [
		'score' => 'integer',
	];

	// Parameter configuration with colors for each score
	public static function getParameterConfig()
	{
		return [
			'rainfall' => [
				'label' => 'Curah Hujan',
				'weight' => 0.30,
				'colors' => [
					1 => '#d9f0a3',  // Light green
					2 => '#91cf60',
					3 => '#1d9641',
					4 => '#de2d26',
					5 => '#8b0000'   // Dark red
				]
			],
			'slope' => [
				'label' => 'Kemiringan',
				'weight' => 0.15,
				'colors' => [
					1 => '#e6f3ff',  // Light blue
					2 => '#99d9ff',
					3 => '#4da6ff',
					4 => '#0066ff',
					5 => '#003d99'   // Dark blue
				]
			],
			'land_use' => [
				'label' => 'Penggunaan Lahan',
				'weight' => 0.15,
				'colors' => [
					1 => '#fff9e6',  // Light yellow
					2 => '#ffe6b3',
					3 => '#ffcc80',
					4 => '#ff9933',
					5 => '#cc6600'   // Dark orange
				]
			],
			'soil_type' => [
				'label' => 'Jenis Tanah',
				'weight' => 0.15,
				'colors' => [
					1 => '#f5f5dc',  // Beige
					2 => '#dcd6b8',
					3 => '#c4b89a',
					4 => '#8b7355',
					5 => '#654321'   // Dark brown
				]
			],
			'rivers' => [
				'label' => 'Sungai',
				'weight' => 0.15,
				'colors' => [
					1 => '#e0f2f1',  // Light cyan
					2 => '#80deea',
					3 => '#4dd0e1',
					4 => '#26c6da',
					5 => '#00838f'   // Dark cyan
				]
			],
			'elevation' => [
				'label' => 'Elevasi',
				'weight' => 0.10,
				'colors' => [
					1 => '#f3e5f5',  // Light purple
					2 => '#e1bee7',
					3 => '#ce93d8',
					4 => '#ba68c8',
					5 => '#8e24aa'   // Dark purple
				]
			],
		];
	}

	public static function getColorForScore($parameterType, $score)
	{
		$config = self::getParameterConfig();
		return $config[$parameterType]['colors'][$score] ?? '#cccccc';
	}

	public static function getWeight($parameterType)
	{
		$config = self::getParameterConfig();
		return $config[$parameterType]['weight'] ?? 0;
	}

	public static function getLabel($parameterType)
	{
		$config = self::getParameterConfig();
		return $config[$parameterType]['label'] ?? ucfirst(str_replace('_', ' ', $parameterType));
	}
}
