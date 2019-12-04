// this updates the airport IDs with a K in front, in order to join up to the airports table
// this should be run periodically to fix when pilots put in a 3-letter code (LGA instead of KLGA)
-- what will be changed (where column 2 is 1)
select pf_airport_id, pf_airport_id REGEXP '[a-zA-Z]{3}' , upper( concat('K', trim(pf_airport_id)) )
from forum_dev_01.phpbb_profile_fields_data
where pf_pilot_yn = 1 
  and pf_airport_id != '' 
  and length(pf_airport_id) < 4
order by pf_airport_id;

--update
update phpbb_profile_fields_data
set pf_airport_id = upper( concat('K', trim(pf_airport_id)) )
where pf_airport_id REGEXP '[a-zA-Z]{3}' 
  and pf_pilot_yn = 1 
  and pf_airport_id != ''
  and length(pf_airport_id) < 4;
