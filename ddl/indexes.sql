create index idx_airports_apt_id_upper on airports (UPPER(apt_id));
create index idx_pf_airport_id_upper on phpbb_profile_fields_data (UPPER(pf_airport_id));