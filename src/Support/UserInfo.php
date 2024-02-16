<?php

namespace NIIT\ESign\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Fluent;

class UserInfo
{
    protected ?string $ip = null;

    public function __invoke(Request $request): JsonResponse
    {
        $response = $this->get($request);

        return response()->json([
            'status' => (int) $response,
            'userInfo' => $response ?? [],
        ]);
    }

    public function get(Request $request): Fluent|false
    {
        $data = $this->process($request);

        $position = new Fluent();

        if ($data instanceof Fluent && ! $this->isEmpty($data)) {
            $position->countryName = $data->country;
            $position->countryCode = $data->countryCode;
            $position->regionCode = $data->region;
            $position->regionName = $data->regionName;
            $position->cityName = $data->city;
            $position->zipCode = $data->zip;
            $position->latitude = (string) $data->lat;
            $position->longitude = (string) $data->lon;
            $position->areaCode = $data->region;
            $position->timezone = $data->timezone;
            $position->currencyCode = $data->currency;
            $position->ip = $this->getIp($request);
        }

        if (! $position->isEmpty()) {
            return $position;
        }

        return false;
    }

    public function url(string $ip): string
    {
        return "http://ip-api.com/json/$ip?lang=en&fields=status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,reverse,mobile,proxy,hosting,query";
    }

    public function process(Request $request): Fluent|false
    {
        return rescue(fn () => new Fluent(
            $this->http()
                ->throw()
                ->acceptJson()
                ->get($this->url($this->getIp($request)))
                ->json()
        ), false, false);
    }

    protected function http(): PendingRequest
    {
        $callback = fn ($http) => $http;

        return value($callback, Http::timeout(2)->connectTimeout(2));
    }

    protected function isEmpty(Fluent $data): bool
    {
        return empty(array_filter($data->getAttributes()));
    }

    protected function getIp(Request $request): string
    {
        if ($this->ip) {
            return $this->ip;
        }

        if (app()->environment('local')) {
            return $this->ip = '117.211.219.128';
        }

        return $this->ip = $request->ip();
    }
}
