<?php

namespace ReactSkillSpace\LaravelBizon365\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class LaravelBizon365Controller extends Controller
{
	public array $methods = [
		'login'            => 'auth/login',

		'getSubpages'      => 'webinars/subpages/getSubpages',
		'getSubscribers'   => 'webinars/subpages/getSubscribers',
		'addSubscriber'    => 'webinars/subpages/addSubscriber',
		'removeSubscriber' => 'webinars/subpages/removeSubscriber',

		'getlist'          => 'webinars/reports/getlist',
		'get'              => 'webinars/reports/get',
		'getviewers'       => 'webinars/reports/getviewers',
	];

	public function __construct(
		public string $token = '',
		public string $url = "https://online.bizon365.ru/api/v1/"
	) {}

	public function request( string $method, array $data, string $type = 'get' ): Object
	{
		return Http::withHeaders([
			'X-Token' => $this->token,
		])->$type( $this->url . $this->methods[ $method ], $data );
	}

	public function getSubpages( array $data = [] ): Object
	{
		$data = validator( $data, [
			'skip' => [ 'numeric', 'min:0', 'nullable' ],
			'limit' => [ 'numeric', 'min:1', 'max:50', 'nullable' ],
		])->validate();
		$subpages = $this->request( 'getSubpages', $data )->object();
		return $subpages;
	}

	public function getAllSubpages( array $data = [] ): Object
	{
		$data[ 'skip' ] = $data[ 'skip' ] ?? 0;
		$data[ 'limit' ] = $data[ 'limit' ] ?? 50;

		$allSubpages = $this->getSubpages( $data );

		for( $i = 0; $i < $allSubpages->total / $allSubpages->limit; $i++ ) {
			$allSubpages->skip = $data[ 'skip' ] = $allSubpages->skip + $allSubpages->limit;
			$slice = $this->getSubpages( $data );
			$allSubpages->pages = array_merge( $allSubpages->pages, $slice->pages );
			$allSubpages->rooms = object_merge( $allSubpages->rooms, $slice->rooms );
		}

		$allSubpages->skip = 0;
		$allSubpages->limit = $allSubpages->total;

		return $allSubpages;
	}

	public function getSubscribers( array $data ): Object
	{
		$data = validator( $data, [
			'pageId' => [ 'required', 'string' ],
			'registeredTimeMin' => [ 'string', 'nullable' ],
			'registeredTimeMax' => [ 'string', 'nullable' ],
			'webinarTimeMin' => [ 'string', 'nullable' ],
			'webinarTimeMax' => [ 'string', 'nullable' ],
			'url_marker' => [ 'string', 'nullable' ],
			'skip' => [ 'numeric', 'min:0', 'nullable' ],
			'limit' => [ 'numeric', 'min:1', 'max:1000', 'nullable' ],
		])->validate();
		$subscribers = $this->request( 'getSubscribers', $data )->object();
		return $subscribers;
	}

	public function getAllSubscribers( array $data ): Object
	{
		$data[ 'skip' ] = $data[ 'skip' ] ?? 0;
		$data[ 'limit' ] = $data[ 'limit' ] ?? 1000;

		$allSubscribers = $this->getSubscribers( $data );

		for( $i = 0; $i < $allSubscribers->total / $allSubscribers->limit; $i++ ) {
			$allSubscribers->skip = $data[ 'skip' ] = $allSubscribers->skip + $allSubscribers->limit;
			$slice = $this->getSubscribers( $data );
			$allSubscribers->list = array_merge( $allSubscribers->list, $slice->list );
		}

		$allSubscribers->skip = 0;
		$allSubscribers->limit = $allSubscribers->total;

		return $allSubscribers;
	}

	public function addSubscriber( array $data = [] ): Object
	{
		$data = validator( $data, [
			'pageId' => [ 'required', 'string' ],
			'email' => [ 'required', 'email' ],
			'phone' => [ 'string', 'nullable' ],
			'time' => [ 'string', 'nullable' ],
			'username' => [ 'string', 'nullable' ],
			'confirm' => [ 'numeric', 'nullable' ],
			'url_marker' => [ 'string', 'nullable' ],
			'utm_source' => [ 'string', 'nullable' ],
			'utm_medium' => [ 'string', 'nullable' ],
			'utm_campaign' => [ 'string', 'nullable' ],
			'utm_term' => [ 'string', 'nullable' ],
			'utm_content' => [ 'string', 'nullable' ],
			'utm_keyword' => [ 'string', 'nullable' ],
			'utm_banner' => [ 'string', 'nullable' ],
			'utm_phrase' => [ 'string', 'nullable' ],
			'utm_group' => [ 'string', 'nullable' ],
			'autoNext' => [ 'numeric', 'nullable' ],
		])->validate();
		return $this->request( 'addSubscriber', $data, 'post' )->object();
	}

	public function removeSubscriber( array $data = [] ): Object
	{
		$data = validator( $data, [
			'pageId' => [ 'required', 'string' ],
			'email' => [ 'required', 'email' ],
		])->validate();
		return $this->request( 'removeSubscriber', $data, 'post' )->object();
	}

	public function getListReports( array $data = [] ): Object
	{
		$data = validator( $data, [
			'skip' => [ 'numeric', 'min:0', 'nullable' ],
			'limit' => [ 'numeric', 'min:1', 'max:100', 'nullable' ],
			'type' => [ 'string', Rule::in([ 'LiveWebinars', 'AutoWebinars' ]), 'nullable' ],
			'minDate' => [ 'string', 'nullable' ],
			'maxDate' => [ 'string', 'nullable' ],
		])->validate();

		$listReports = $this->request( 'getlist', $data )->object();

		foreach( $listReports->list as $report ) {
			$report->data = json_decode( $report->data );
		}

		return $listReports;
	}

	public function getListAllReports( array $data = [] ): Object
	{
		$data[ 'skip' ] = $data[ 'skip' ] ?? 0;
		$data[ 'limit' ] = $data[ 'limit' ] ?? 100;
		$listReports = $this->getListReports( $data );

		for( $i = 0; $i < $listReports->count / $listReports->limit; $i++ ) {
			$listReports->skip = $data[ 'skip' ] = $listReports->skip + $listReports->limit;
			$slice = $this->getListReports( $data );
			$listReports->rooms = array_merge( $listReports->rooms, $slice->rooms );
			$listReports->list = array_merge( $listReports->list, $slice->list );
		}

		$listReports->skip = 0;
		$listReports->limit = $listReports->count;

		return $listReports;
	}

	public function getReport( array $data = [] ): Object
	{
		$data = validator( $data, [
			'webinarId' => [ 'required', 'string' ],
		])->validate();

		$request = $this->request( 'get', $data )->object();

		$request->report->report = json_decode( $request->report->report );
		$request->report->messages = json_decode( $request->report->messages );
		$request->report->messagesTS = json_decode( $request->report->messagesTS );

		return $request;
	}

	public function getViewers( array $data = [] ): Object
	{
		$data = validator( $data, [
			'skip' => [ 'numeric', 'min:0', 'nullable' ],
			'limit' => [ 'numeric', 'min:1', 'max:1000', 'nullable' ],
			'webinarId' => [ 'required', 'string' ],
		])->validate();

		return $this->request( 'getviewers', $data )->object();
	}

	public function getAllViewers( array $data = [] ): Object
	{
		$data[ 'skip' ] = $data[ 'skip' ] ?? 0;
		$data[ 'limit' ] = $data[ 'limit' ] ?? 1000;
		$listViewers = $this->getViewers( $data );

		for( $i = 0; $i < $listViewers->total / $listViewers->limit; $i++ ) {
			$listViewers->skip = $data[ 'skip' ] = $listViewers->skip + $listViewers->limit;
			$slice = $this->getViewers( $data );
			$listViewers->viewers = array_merge( $listViewers->viewers, $slice->viewers );
		}

		$listViewers->skip = 0;
		$listViewers->limit = $listViewers->total;
		$listViewers->loaded = count( $listViewers->viewers );

		return $listViewers;
	}
}