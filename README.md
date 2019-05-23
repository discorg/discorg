# finder-keeper
= music finder and keeper

Finder Keeper is an app that helps me find and track the music I listen to. The goals are in two major categories:

**Finder**
* aggregate sources of new music releases such as rateyourmusic.com, aoty.com, pitchfork.com, www.sputnikmusic.com
* show new findings in a format that is tailored for my needs
* allow adding found albums to Keeper

**Keeper**
* music library functionality
* work on album-level (not songs level as spotify)
* enhance Spotify (e.g. album tags)
* bypass Spotify limitations (e.g. max library size)
* retain information about source of music from Finder


Tasks for now:
- [x] import existing code from spotify-client
- [x] setup stytic analysis and code style rules
- [ ] setup tech stack - services container, HTTP stack
- [ ] setup happy-path HTTP end-to-end test
- [ ] refactoring of existing code - setup application architecture (layers), extract infrastructure
- [ ] add keeper MVP functionality
- [ ] release version 0.1

**Keeper MVP**
* multi-user web app with server-side rendering
* connect to spotify
* download list of albums from spotify to user library on demand
* store albums by spotify id
* list albums in library, link to spotify for listening
* remove album from library

**Tech**
* static code analysis
    * phpstan + strict rules
    * checked exceptions
    * strict coding standard rules
* HTTP stack
    * PSR-7 request/response
    * PSR-15 middleware stack
    * https://github.com/Nyholm/psr7
    * https://github.com/middlewares/awesome-psr15-middlewares
    * https://github.com/middlewares/psr15-middlewares
    * https://github.com/thephpleague/route
* architecture
    * strictly separated domain, application and infrastructure layers
    * command bus, query bus, (async) events
    * DDD - aggregates, repositories, value objects
* testing
    * end-to-end tests using HTTP request/response
    * behat/gherkin for acceptance testing of behaviour
    * proper unit tests (no mocks, no brittle single-class tests)
