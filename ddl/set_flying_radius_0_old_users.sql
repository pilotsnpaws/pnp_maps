-- this query sets flying radius to 0 where the user hasnt visited in a long time

-- first, run this with new dates to verify what you're doing
-- ie, set this back 6 or 9 months https://www.unixtimestamp.com/index.php 
select * from phpbb_profile_fields_data
where pf_flying_radius > 0 
and user_id IN
(select u.user_id from phpbb_users u 
where user_lastvisit < UNIX_TIMESTAMP(STR_TO_DATE('Jan 01 2021 10:00PM', '%M %d %Y %h:%i%p'))  and user_type = 0 );

select * from phpbb_users u 
where user_lastvisit < 1609484401 and user_regdate < 1622527201 and user_id = 13533;

-- select * from update phpbb_profile_fields_data
update phpbb_profile_fields_data
set pf_flying_radius = 0
where pf_flying_radius > 0  
and user_id IN
(select u.user_id from phpbb_users u 
where user_lastvisit < 1609484401 and user_regdate < 1609484401 );