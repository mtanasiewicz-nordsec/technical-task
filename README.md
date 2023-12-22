## Architecture
Decided to proceed with classic, non-modular symfony architecture with one exception:
introducing vendor-agnostic approach. Using any advanced concepts, like
* layered architecture
* ports & adapters
* CQRS
* modular approach

would be an overkill for such a small project. At this moment if client decides to further
develop this app, all of the above can be quickly introduced.

### Solution
I have decided to modify the api a bit and reduce the application to single entry point without
the need to create separate endpoints for every new service provider. This can be easily solved by
single queryParam with service provider key as in this solution. Adding new geocoding provider is as
simple as following these steps:
1. Add enum case in GeocodingServiceProvider with name of the service provider.
2. Add implementation of GeocoderInterface in separate folder.
3. Add unit test for new implementation similar to HereMapsGeocoderTest.php.
4. And that's all...

### Vendor agnostic approach
For me it's one of the most important architectural aspects of every application. All libraries that we use in
our domain, should be wrapped in Facades, that residue in separate Tooling directory and should never be directly used in 
domain specific code. This significantly reduces code coupling, and leaves us with possibility to quickly exchange
libraries in case of bugs or dropped developer support. Although the application is still coupled to Symfony framework
for example in Controllers, I accept this coupling because switching frameworks is extremely rare.

### Tests
Tests consist of 3 layers:

#### Unit tests
Used for tooling and specific implementations, only there where they are needed. I'm not a fan of covering every class
with corresponding Unit tests, as this stiffens the code too much. If we have an application service, that uses lots of
DI injected services, and we cover all of them with Units test, whenever we would like to introduce some refactoring,
naming changes or class changes inside this stack, those tests would immediately fail. If we cover the application
services with integration tests, and just test for inputs and outputs, mocking
only stuff that's really should/could be mocked like external API calls, than introducing any refactoring would leave
those tests green as long as the whole stack still does what it did before.

#### Integration tests
Used for testing of Application specific logic with built container. Tests the application logic with all the components running under
the hood. **In this case I mocked the database calls, to save some time with database cleanup configuration and fixtures**.
Normally I would include all calls to internal ecosystem, as long as they are within our controllable stack.

#### API tests (Not implemented)
Similar to Integration tests, but using actual API calls. I've decided not to implement this layer as this is material for
another story. Configuring this layer in php is really complex and time-consuming if you want to do it right.
