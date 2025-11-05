********* DOCUMENTATION **********

1. The /index.php is the main entry point which links to Admin, Student, and ZRP Police logins.
2. The /includes/db.php is the connection file which connects to the database securely using pdo
3. The /includes/auth.php handles Admin login from /public/admin-login.php. For ZRP Police, use /includes/police_auth.php and /public/police-login.php. Each has its own session keys.
4. The /public/dashboard.php is the main admin dashboard where admins can see cases reported/submitted by students.
5. The /public/update_case.php updates case status (Open, Investigating, Escalated, Resolved, Closed), can escalate to police, assign to police, and send messages to police.
6. The /public/close_case.php is responsible for closing the case in the admin dashboard
7. The /public/police-login.php allows ZRP officers (from police_staff table) to log in. The /public/police_dashboard.php shows escalated or assigned cases. /public/police_update_case.php lets police update case status and message Admin. /public/police_take_case.php assigns a case to the logged-in officer.
8. Lastly, the /public/logout.php is responsible for logging out of the dashboard safely by destroying active login sessions.

Database changes:
- staff: admins only
- police_staff: separate ZRP officers table (email, username, password, name)
- cases: added assigned_police_id (INT NULL)
- case_messages: new table storing admin–police messages (case_id, sender_type, sender_id, message, created_at)
- Seed users: admin@example.com/admin123 and police@example.com/police123