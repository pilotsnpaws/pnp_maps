-- this query sets flying radius to 0 where the user hasnt visited in a long time

-- first, run this with new dates to verify what you're doing
-- ie, this sets it for users who have not visited since before Jul 1 2022, and registered before Jan 1 just in case
select u.user_id from phpbb_users u JOIN phpbb_profile_fields_data ud ON u.user_id = ud.user_id
where 1=1
	and user_lastvisit < UNIX_TIMESTAMP(STR_TO_DATE('Jul 01 2024 10:00PM', '%M %d %Y %h:%i%p'))
      and user_regdate < UNIX_TIMESTAMP(STR_TO_DATE('Jan 01 2024 10:00PM', '%M %d %Y %h:%i%p'))
	AND ud.pf_flying_radius > 0
order by u.user_id desc;

-- do the update
update phpbb_profile_fields_data
set pf_flying_radius = 0
where pf_flying_radius > 0  
and user_id IN
(select u.user_id from phpbb_users u JOIN phpbb_profile_fields_data ud ON u.user_id = ud.user_id
where 1=1
	and user_lastvisit < UNIX_TIMESTAMP(STR_TO_DATE('Jul 01 2024 10:00PM', '%M %d %Y %h:%i%p'))
      and user_regdate < UNIX_TIMESTAMP(STR_TO_DATE('Jan 01 2024 10:00PM', '%M %d %Y %h:%i%p'))
	AND ud.pf_flying_radius > 0
  );
