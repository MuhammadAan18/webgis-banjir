<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('spatial_parameters', function (Blueprint $table) {
			$table->id();
			$table->enum('parameter_type', ['rainfall', 'slope', 'land_use', 'soil_type', 'rivers', 'elevation']);
			$table->string('parameter_name')->nullable(); // e.g., "High Rainfall Area"
			$table->tinyInteger('score')->unsigned(); // 1-5
			$table->geometry('geom'); // PostGIS geometry
			$table->timestamps();

			// Indexes
			$table->index('parameter_type');
			$table->index('score');
			$table->spatialIndex('geom');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('spatial_parameters');
	}
};
