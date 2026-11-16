OmulPotrivit ToDO:

**1.Create the Services Layer \& Complexity Features (+2.0 p Services \& Complexity) DONE**



&#x20;Create app/Services/ListingService.php and app/Services/CategoryService.php (injecting ListingAccessor.php and CategoryAccessor.php via Constructor DI).

&#x20;Add Cache (Cache::remember / Cache::forget) for categories, home listings, and promotion plans.

&#x20;Add Logging (Log::info / Log::warning) for listing creation, promotion, quota blocks, and deletions.

&#x20;Enforce Logical Deletion (status = 'inactiv' on Listing, is\_hidden = true on Review) and filter out inactive/hidden items in public queries.



**2.Move Business Logic \& Main Page (GET /) to Services (+1.5 p Business Logic) DONE**



&#x20;Move canUserCreateListing() from ListingController.php into ListingService.

&#x20;Replace the route closure on GET / in web.php with a Controller method calling ListingService::getHomePageListings() (filtering status = 'activ' and ranking is\_featured = true first) and CategoryService::getAllCategories().



**3.Complete Admin CRUD on 2 Entities with Foreign Key (+0.75 p Admin) DONE**



&#x20;Add Create (create + store) for User and Listing in AdminController.php and web.php.

&#x20;Add the foreign key dropdown (category\_id / provider\_id) to admin/listings/edit.blade.php and the new Admin create form.

&#x20;Refactor AdminController.php to use Services/Accessors instead of direct Eloquent calls.



**4.Implement User Section with Plans \& API Controls (+0.75 p User API Section) DONE**



&#x20;Seed PromotionPlan.php in DatabaseSeeder.php (e.g., Free, Pro 7 Days, Business 30 Days).

&#x20;Add API endpoints in api.php \& Api/ListingController.php: GET /api/plans, POST /api/listings/{id}/promote, and PATCH /api/listings/{id}/toggle-status (or logical DELETE /api/listings/{id}).

&#x20;Update my-listings.blade.php to fetch and display plans and trigger promotion / status controls via fetch / axios calls to /api/....



**Add Event-Driven Communication with 2 Listeners (+1.0 p Bonus)**



&#x20;Create event app/Events/ListingPromoted.php (dispatched from ListingService when a listing is promoted).

&#x20;Create Listener 1 (app/Listeners/SendPromotionNotification.php) -> saves a notification in AppNotification.php.

&#x20;Create Listener 2 (app/Listeners/RecordPromotionStatEvent.php) -> records an analytics entry in StatEvent.php and logs it.



**Documentation \& Cleanup (+0.5 p Documentation)**



&#x20;Fix author\_id = auth()->id() in ReviewController.php and ownership checks in ListingController.

&#x20;Replace default README.md with the structured project documentation following your course template and update PROJECT\_ROADMAP.md.

