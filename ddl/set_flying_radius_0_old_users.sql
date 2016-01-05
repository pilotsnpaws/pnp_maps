-- this query sets flying radius to 0 where the user hasnt visited in a long time

-- select * from update phpbb_profile_fields_data
update phpbb_profile_fields_data
set pf_flying_radius = 0
where pf_flying_radius > 0
and user_id IN
(select u.user_id from phpbb_users u join phpbb_profile_fields_data p on u.user_id = p.user_id 
		where user_lastvisit < 1414800000 and user_regdate < 1414800000 and pf_flying_radius > 0)
