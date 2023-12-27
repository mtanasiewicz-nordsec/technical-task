# Geocoders technical documentation

## Overview
All specific geocoder implementations residue in `App/Core/Service/Geocoder` namespace
and are divided into separate folders per service provider. All of them implement common interface
which is then consumed by the main application service `App/Core/Service/GeocoderService`.

### Application service
The application service has one simple method exposed as interface which takes 2 arguments:
* Address representation object - all the data needed to geocode this address.
* Array of service provider names. This argument can be passed 3 different ways with results as follows:
  * empty array: The service will query only the cache.
  * array with single value: The service will query only this service provider
  * array with multiple values: The service will query all of passed service providers in the order they were passed, until it finds first result.
* All Api results are cached in database, so if user issues same query over time, the external APIs won't be called.
* Cache invalidation is handled by simply clearing the database from records older than the `COORDINATES_CACHE_VALIDITY_IN_MINUTES` .env value. To clear the cache simply run:
```shell
make invalidate-geocoders-cache
```

## Steps to implement new Geocoder
1. Implement the `App\Core\Service\Geocoder\GeocoderInterface` in a separate folder dedicated to this provider
2. Add corresponding `App\Core\Enum\GeocodingServiceProvider` case with the providers name.
3. Add Unit test for this Geocoding provider.
4. And that's all you really have to do.
