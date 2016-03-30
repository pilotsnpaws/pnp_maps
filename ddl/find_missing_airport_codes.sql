use pnp_forum1;

-- run this to find out what airport codes are missing
select count(u.user_id), p.pf_airport_id -- , a.apt_id
from phpbb_profile_fields_data p
	left join airports a on p.pf_airport_id = a.apt_id
    join phpbb_users u on p.user_id = u.user_id
where user_type = 0
	and p.pf_pilot_yn = 1
	and p.pf_airport_id is not null 
    and p.pf_airport_id != ''
    and a.apt_id is null and u.user_lastvisit > 1401675098
group by 2
order by 1,2 desc

-- add data with this
INSERT INTO `airports`
(`apt_id`,
`apt_name`,
`lat`,
`lon`,
`city`,
`state`,
country_code)
VALUES
('T57','Garland/DFW Heloplex', '32.8876250' ,'-96.6836075', 'Garland', 'TX', 'US' ),
('T57','Garland/DFW Heloplex', '32.8876250' ,'-96.6836075', 'Garland', 'TX', 'US' )
;

('K',' Airport', '' ,'', '', 'FL', 'US' )

select * from airports where apt_id = 'KBVU'
delete from airports where id = 12142



select * from phpbb_profile_fields_data where user_id = 8065

    select * from airports where apt_id = 'APA'
    select * from phpbb_profile_fields_data where pf_airport_id = 'APA'
    
update phpbb_profile_fields_data 
set pf_airport_id = 'N10'
where pf_airport_id = 'KN10'