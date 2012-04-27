This README is currently a work in progress, and should be considered extremely
incomplete.

Moving SMAQC from a local development setup to test/production:
1. smaqc/application/config/config.php will need to be reconfigured probably
  a. base_url may need to be set
  b. If mod_rewrite is in use, index_page will need to be set = ''
  
  Theoretically, I think those are the only two things that might need changing
  in config.php.
  
2. The main issue at hand is (I believe) the database setup.
  This version (1.0 currently) was not developed using FreeTDS at all.
  We're a little unsure what changes using FreeTDS makes to accessing the DB
  using CodeIgniter, but the smaqc/application/config/database.php file will
  obviously have to be changed to point to the correct DB and use the correct
  credentials. Currently, we just use the mssql driver, and all DB access is
  handled through the Active Record class. If for some reason that method
  (using the Active Record class) of accessing the DB does not work for the
  test/production environment, we'll need to rewrite a decent chunk of the code
  before it will be usable. It should be relatively simple, but not quite
  trivial.
