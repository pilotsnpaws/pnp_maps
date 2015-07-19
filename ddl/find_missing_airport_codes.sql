-- run this to find out what airport codes are missing
select count(u.user_id), p.pf_airport_id -- , a.apt_id
from phpbb_profile_fields_data p
	left join airports a on p.pf_airport_id = a.apt_id
    join phpbb_users u on p.user_id = u.user_id
where user_type = 0
	and p.pf_pilot_yn = 1
	and p.pf_airport_id is not null 
    and p.pf_airport_id != ''
    and apt_id is null and u.user_regdate > 1401675098
group by 2

-- add data with this
INSERT INTO `pnp_forum`.`airports`
(`apt_id`,
`apt_name`,
`lat`,
`lon`,
`city`,
`state`,
country_code)
VALUES
('KFIN','Flagler County Airport', '29.4657003' ,'-81.2087181', 'Palm Coast', 'FL', 'US' ),
('KRCE','Clarence E Page Municipal Airport', '35.4880833' ,'-97.8235556', 'Oklahoma City', 'OK', 'US' ),
('KRTS','Reno/Stead Airport', '39.6681769' ,'-119.8764396', 'Reno', 'NV', 'US' ),
('KSJS','Big Sandy Regional Airport', '37.7510278' ,'-82.6366944', 'Prestonsburg', 'KY', 'US' )
;

('K',' Airport', '' ,'', '', 'FL', 'US' )

select * from airports where apt_id = 'KADF'
delete from airports where id = 42760


select * from phpbb_profile_fields_data where user_id = 8065


    
    select * from airports where apt_id = 'O69'
    select * from phpbb_profile_fields_data where pf_airport_id = 'K4b0'
    
    update airports set country_code = 'US' 