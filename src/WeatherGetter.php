<?php

    namespace WeatherGetter;

    use IPStack\PHP\GeoLookup;
    use GuzzleHttp\Client;
    use WeatherGetter\Factories\WeatherServiceFactory;

    class WeatherGetter {

        private $weather_service;

        public function __construct() {
            $weather_service_factory = new WeatherServiceFactory();
            $this->weather_service = $weather_service_factory->getService("OpenWeatherMap");
        }

        public function get($city = false) {
            if (!$city) {
                $city = $this->getCurrentCity();
            }

            return $this->weather_service->getWeather($city);
        }

        private function getCurrentCity() {
            $geo_lookup = new GeoLookup(getenv('IPSTACK_API_KEY'), false, 10);

            $my_ip = $this->getMyIp();

            $location = $geo_lookup->getLocationFor($my_ip);

            if (!empty($location->city)) {
                return $location->city;
            }

            $capital_city = $location->location->capital;

            if (empty($capital_city)) {
                throw new \Exception("No capital or city found from IP");
            };

            return $capital_city;
        }

        private function getMyIp() {
            $client = new Client();
            $response = $client->request('GET', 'http://icanhazip.com/');

            $my_ip = trim($response->getBody()->getContents());

            if (!filter_var($my_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                throw new \Exception("IP couldn't be detected");
            }

            return $my_ip;
        }
    }