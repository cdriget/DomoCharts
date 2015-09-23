--------------------------------------------------
-- Button  : 2 => Power
-- Author  : Lazer
-- Version : 5.0
-- Date    : September 2015
--------------------------------------------------

-- System variables
local debug = false
local selfID = fibaro:getSelfId()
local ip = fibaro:get(selfID, 'IPAddress')
local port = fibaro:get(selfID, 'TCPPort')
local NAS = Net.FHttp(ip, tonumber(port))
local erreur = 0

-- Send data to NAS (SQL DB)
function SendDataNAS (datas)
	if debug then
		fibaro:debug(json.encode(datas))
	end
	if #datas > 0 then
		local payload = "/graph/data_post.php"
		local response, status, errorCode = NAS:POST(payload, json.encode(datas))
		if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
			jsonTable = json.decode(response);
			if jsonTable.success ~= true then
				erreur = erreur + 1
				fibaro:debug('<span style="display:inline;color:red;">Error '..(jsonTable.error and jsonTable.error.code or "???")..' : '..(jsonTable.error and jsonTable.error.message or "???")..'</span>')
			elseif debug then
				fibaro:debug('<span style="display:inline;color:green;">OK : '..(jsonTable.rowcount or "???")..' lines inserted in DB</span>')
			end
		else
			erreur = erreur + 1
			fibaro:debug('<span style="display:inline;color:red;">Error : Can not connect to NAS, errorCode='..errorCode..', status='..status..', payload='..payload..', response='..(response or "")..'</span>')
		end
	end
end

-- Get HC2 software version
local HC2 = Net.FHttp("127.0.0.1", 11111)
payload = "/api/settings/info"
response, status, errorCode = HC2:GET(payload)
if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
	jsonTable = json.decode(response)
	if tonumber(jsonTable.softVersion) >= 4 then
		version = 4
	else
		version = 3
	end
	if debug then
		fibaro:debug("v"..version)
	end
	-- Get HC2 Device list
	payload = "/api/devices"
	response, status, errorCode = HC2:GET(payload)
	if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then

		-- Get data
		local datas = {}
		jsonTable = json.decode(response)
		for i = 1, #jsonTable do
			if version == 4 then
				-- Exclude hidden, disabled, and dead devices
				if jsonTable[i].interfaces and jsonTable[i].visible and jsonTable[i].visible == true and jsonTable[i].enabled and jsonTable[i].enabled == true and jsonTable[i].properties.dead and jsonTable[i].properties.dead == "false" then
					-- Look for devices returning power consumption
					for j = 1, #jsonTable[i].interfaces do
						if jsonTable[i].interfaces[j] == "power" then
							local power = jsonTable[i].properties.power
							if debug then
								fibaro:debug(jsonTable[i].id.." "..jsonTable[i].name.." "..power.."W")
							end
							datas[#datas+1] = {}
							datas[#datas].id = jsonTable[i].id
							datas[#datas].timestamp = 'NULL'
							datas[#datas].type = "power"
							datas[#datas].value = power
							break
						end
					end
				end
			elseif version == 3 then
				-- Look for devices returning power consumption
				if jsonTable[i].properties.unitSensor and jsonTable[i].properties.unitSensor == "W" then
					power = jsonTable[i].properties.valueSensor
					if debug then
						fibaro:debug(jsonTable[i].id.." "..jsonTable[i].name.." "..power.."W")
					end
					datas[#datas+1] = {}
					datas[#datas].id = jsonTable[i].id
					datas[#datas].timestamp = 'NULL'
					datas[#datas].type = "power"
					datas[#datas].value = power
				end
			end
		end

		-- Send data to NAS
		SendDataNAS(datas)

	else
		erreur = erreur + 1
		fibaro:debug('<span style="display:inline;color:red;">status='..status..', errorCode='..errorCode..', payload='..payload..', response='..(response or "")..'</span>')
	end
else
	erreur = erreur + 1
	fibaro:debug('<span style="display:inline;color:red;">status='..status..', errorCode='..errorCode..', payload='..payload..', response='..(response or "")..'</span>')
end

if erreur > 0 then
	fibaro:log("Erreur")
else
	fibaro:log("Power uploaded")
end
