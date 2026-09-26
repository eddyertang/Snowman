/*
 * Live settings. Leave supabaseUrl empty to keep the site in demo mode.
 * The publishable (anon) key is safe to ship in a web page: the database only
 * lets it INSERT into the waitlist (see backend/migrations/001_waitlist.sql).
 * Never put the service_role / secret key here.
 */
window.CC_CONFIG = {
  supabaseUrl: "",
  supabaseKey: "",
};
