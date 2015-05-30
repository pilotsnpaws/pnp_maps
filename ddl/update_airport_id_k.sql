// this updates the airport IDs with a K in front, in order to join up to the airports table
// this should be run periodically to fix when pilots put in a 3-letter code (LGA instead of KLGA)

update phpbb_profile_fields_data
set pf_airport_id = concat('K',pf_airport_id)
where length(pf_airport_id) = 3
and pf_airport_id regexp '^[A-Z]+$'
and left(trim(pf_airport_id),1) != 'Y'