-- show trend of users last visit
-- supports https://docs.google.com/spreadsheet/ccc?key=0AqADtcuKKbN8dFN0UnU1cWdZdk82cFdWaWdyVHhCelE&usp=sharing
select date_format(last_visit, '%Y %m'), count(*)
from vw_volunteers 
group by date_format(last_visit, '%Y %m')
order by 1,2