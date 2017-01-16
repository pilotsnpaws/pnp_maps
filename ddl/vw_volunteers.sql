-- view created 2013, MJG. Updated 2016-03-30

-- drop view vw_volunteers
-- drop table vw_volunteers


create view vw_volunteers
AS
select
	('1969-12-31 20:00:00' + interval greatest(u.user_regdate,u.user_lastvisit,u.user_lastmark,u.user_lastpost_time,u.user_last_search) second) AS last_visit,
	date_format(('1969-12-31 20:00:00' + interval u.user_lastvisit second),'%a, %D %b %Y @ %h:%i%p') AS last_visit_human,
	u.user_id AS user_id,
	u.user_regdate AS user_regdate,
	u.username AS username,
    	u.user_email as user_email,
	pf.pf_flying_radius AS pf_flying_radius,
	pf.pf_foster_yn AS pf_foster_yn,
	pf.pf_pilot_yn AS pf_pilot_yn,
	pf.pf_airport_id AS apt_id,
	a.apt_name AS apt_name,
	pf.pf_zip_code AS zip,
	cast(coalesce(a.lat,convert(z.lat using latin1)) as decimal(12,6)) AS lat,
	cast(coalesce(a.lon,convert(z.lon using latin1)) as decimal(12,6)) AS lon,
	coalesce(a.city,convert(z.city using latin1)) AS city,
	coalesce(a.state,convert(z.state using latin1)) AS state,
	u.user_inactive_reason, 
	u.user_type
from phpbb_users u
	join phpbb_profile_fields_data pf on u.user_id = pf.user_id
	left join airports a on a.apt_id = pf.pf_airport_id
	/* airport IDs are mixed case when user enters them, need to upper case occasionally, joining on UPPER is SLLLOOOW */
	left join zipcodes z on z.zip = pf.pf_zip_code
where 1=1
