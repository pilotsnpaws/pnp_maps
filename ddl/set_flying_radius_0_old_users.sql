-- this query sets flying radius to 0 where the user hasnt visited in a long time

-- select * from update phpbb_profile_fields_data
update phpbb_profile_fields_data
set pf_flying_radius = 0
where pf_flying_radius > 0
and user_id IN
(select u.user_id from phpbb_users u 
where user_lastvisit < 1484668328 and user_regdate < 1484668328 )