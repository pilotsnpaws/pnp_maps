-- first, copy the current airport ID to the backup field, 
-- where the backup code is empty, but there is a present airport code
update prod_forum.phpbb_profile_fields_data
	set pf_airport_id_backup = upper(pf_airport_id)
	where 1=1
		and (pf_airport_id_backup = '' or pf_airport_id_backup is null)
		and (pf_airport_id != '' and pf_airport_id is not null)
		and user_id < 100000;
  
-- then, do zip codes as well
update prod_forum.phpbb_profile_fields_data
	set pf_zip_code_backup = pf_zip_code
	where 1=1
		and (pf_zip_code_backup = '' or pf_zip_code_backup is null)
		and (pf_zip_code != '' and pf_zip_code is not null)
		and user_id < 100000;
  
  
-- set the airport code to null where the user has not logged in since the date specified
update prod_forum.phpbb_profile_fields_data
  set pf_airport_id = null
  where 1=1
	and (pf_airport_id is not null and pf_airport_id != '')
	and user_id in (
		select user_id 
		from prod_forum.phpbb_users
		where 1=1
		  and user_lastvisit < UNIX_TIMESTAMP(STR_TO_DATE('Jan 31 2020 10:00PM', '%M %d %Y %h:%i%p'))
			and user_regdate < UNIX_TIMESTAMP(STR_TO_DATE('Jan 31 2020 10:00PM', '%M %d %Y %h:%i%p'))
            );
            
-- then zip codes too
update prod_forum.phpbb_profile_fields_data
set pf_zip_code = null
where 1=1
	and (pf_zip_code != '' and pf_zip_code is not null)
	and user_id in (
		select user_id 
		from prod_forum.phpbb_users
		where 1=1
			and user_lastvisit < UNIX_TIMESTAMP(STR_TO_DATE('Jan 31 2020 10:00PM', '%M %d %Y %h:%i%p'))
			and user_regdate < UNIX_TIMESTAMP(STR_TO_DATE('Jan 31 2020 10:00PM', '%M %d %Y %h:%i%p'))
            )
	and user_id < 100000;
	    
-- while we are here, should also set flying distance to 0 for old users
-- see https://github.com/pilotsnpaws/pnp_maps/blob/master/ddl/set_flying_radius_0_old_users.sql
            
            
-- supporting query to see how many will be affected
select * 
from prod_forum.phpbb_profile_fields_data
  where 1=1
	and (pf_airport_id is not null and pf_airport_id != '')
	and user_id in (
		select user_id 
		from prod_forum.phpbb_users
		where 1=1
		  and user_lastvisit < UNIX_TIMESTAMP(STR_TO_DATE('Jan 01 2020 10:00PM', '%M %d %Y %h:%i%p'))
			and user_regdate < UNIX_TIMESTAMP(STR_TO_DATE('Jan 01 2020 10:00PM', '%M %d %Y %h:%i%p'))
            )
order by user_id desc
