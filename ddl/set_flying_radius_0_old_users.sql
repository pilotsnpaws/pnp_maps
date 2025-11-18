-- first, run this with new dates to verify what you're doing
-- ie, this sets it for users who have not visited since before Jul 1 2022, and registered before Jan 1 just in case
select * from phpbb_profile_fields_data
where pf_flying_radius > 0 
and user_id IN
(select u.user_id from phpbb_users u 
where 1=1
	and user_lastvisit 	< UNIX_TIMESTAMP(DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -13 MONTH))
	and user_regdate 	< UNIX_TIMESTAMP(DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -13 MONTH))
	)
order by user_id desc;

-- do the update
update phpbb_profile_fields_data
	set pf_flying_radius = 0
	where pf_flying_radius > 0  
		AND user_id < 1000000
			AND user_id IN
			(select u.user_id from phpbb_users u 
			where 1=1
				and user_lastvisit 	< UNIX_TIMESTAMP(DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -13 MONTH))
				and user_regdate 	< UNIX_TIMESTAMP(DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -13 MONTH))
	);
