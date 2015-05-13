-- this query sets flying radius to 0 where the user hasnt visited in a long time

-- select * from update phpbb_profile_fields_data
update phpbb_profile_fields_data
set pf_flying_radius = 0
where pf_flying_radius > 0
and user_id IN
(select user_id from phpbb_users where user_lastvisit < 1388534400 and user_regdate < 1427846400)
