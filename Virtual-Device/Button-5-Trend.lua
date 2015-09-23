--------------------------------------------------
-- Button  : 5 => Trend
-- Author  : Lazer
-- Version : 5.0
-- Date    : September 2015
--------------------------------------------------

local selfID = fibaro:getSelfId()
local ip = fibaro:get(selfID, 'IPAddress')
local port = fibaro:get(selfID, 'TCPPort')
local NAS = Net.FHttp(ip, tonumber(port))
local payload = "/graph/generate_trend.php"
response, status, errorCode = NAS:GET(payload)

if tonumber(errorCode) == 0 and tonumber(status) == 200 then
	fibaro:log('Trends generated')
else
	fibaro:debug('<span style="display:inline;color:red;">Error : Can not connect to NAS, errorCode='..errorCode..', status='..status..', payload='..payload..', response='..(response or "")..'</span>')
end
