<?php

namespace ReactSkillSpace\LaravelBizon365\App\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelBizon365ServiceProvider extends ServiceProvider
{
	public function register()
	{
		//
	}

	public function boot()
	{
		$this->loadMigrationsFrom( __DIR__ . "/../../database/migrations" );
	}
}