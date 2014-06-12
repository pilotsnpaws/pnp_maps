update phpbb_profile_fields_data
set pf_flying_radius = '' 
where 1=1
and pf_flying_radius > 1000 
and pf_airport_id = ''
and pf_flying_radius != ''

SELECT * FROM `phpbb_config`
where config_name like '%queue%'

update phpbb_config
set config_value = 0
where config_name = 'last_queue_run'

update phpbb_config
set config_value = 0
where config_name = 'cron_lock'

update phpbb_config
set config_value = 10
where config_name = 'email_package_size'

update phpbb_config
set config_value = 50
where config_name = 'email_max_chunk_size'

update phpbb_config
set config_value = 60
where config_name = 'queue_interval'

SELECT * FROM `phpbb_config`
where config_name in ('queue_interval') or config_name like '%email%' 
or config_name like '%interval%' or config_name like '%cron%' or config_name = 'last_queue_run'

6 months
1367370061

select greatest(u.user_regdate, u.user_lastvisit, u.user_lastmark, u.user_lastpost_time, u.user_last_search)
        as last_visit,
u.* from phpbb_users u
where greatest(u.user_regdate, u.user_lastvisit, u.user_lastmark, u.user_lastpost_time, u.user_last_search) < 1367370061
order by greatest(u.user_regdate, u.user_lastvisit, u.user_lastmark, u.user_lastpost_time, u.user_last_search) desc

update phpbb_profile_fields_data_bu
set pf_flying_radius = 0
where pf_flying_radius > 0
and user_id IN 
(select u.user_id 
from phpbb_users u
where greatest(u.user_regdate, u.user_lastvisit, u.user_lastmark, u.user_lastpost_time, u.user_last_search) < 1367370061)

bu = 463946
live = 1058491
429542