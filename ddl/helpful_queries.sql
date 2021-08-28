-- show trend of users last visit
-- supports https://docs.google.com/spreadsheet/ccc?key=0AqADtcuKKbN8dFN0UnU1cWdZdk82cFdWaWdyVHhCelE&usp=sharing
select date_format(last_visit, '%Y %m'), count(*)
from vw_volunteers 
group by date_format(last_visit, '%Y %m')
order by 1,2

-- Deactivate users who haven't logged in or posted in last 18 months
select u.user_id from phpbb_users u 
where 1=1
	and user_lastvisit < UNIX_TIMESTAMP(STR_TO_DATE('Jul 01 2019 10:00PM', '%M %d %Y %h:%i%p'))
	and user_regdate < UNIX_TIMESTAMP(STR_TO_DATE('Jul 01 2019 10:00PM', '%M %d %Y %h:%i%p'))
    and user_type = 0 -- user_type = 0 means active ;
    
    select * from phpbb_users where user_id = 1108;
-- SELECT UNIX_TIMESTAMP(STR_TO_DATE('Jan 01 2020 10:00PM', '%M %d %Y %h:%i%p'));

update phpbb_users u
	set user_type = 1 , user_inactive_reason = 3
where 1=1
	and user_lastvisit < UNIX_TIMESTAMP(STR_TO_DATE('Jul 01 2019 10:00PM', '%M %d %Y %h:%i%p'))
	and user_regdate < UNIX_TIMESTAMP(STR_TO_DATE('Jul 01 2019 10:00PM', '%M %d %Y %h:%i%p'))
    and user_type = 0 -- user_type = 0 means active ;


/* user_type
define('USER_NORMAL', 0);
define('USER_INACTIVE', 1);
define('USER_IGNORE', 2);
define('USER_FOUNDER', 3);
*/