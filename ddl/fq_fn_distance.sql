CREATE DEFINER=`xpilotsnpaws`@`eva.futurequest.net` FUNCTION `fn_distance`(p_fromLat double, p_fromLon double,
	 p_toLat double, p_toLon double) RETURNS double
BEGIN

DECLARE distance float(11,6);

SET distance = (3959 * acos( cos( radians(p_fromLat) ) 
   * cos( radians(p_toLat) ) 
   * cos( radians(p_toLon) - radians(p_fromLon)) + sin(radians(p_fromLat)) 
   * sin( radians(p_toLat)))); 

RETURN round(distance,0);

END