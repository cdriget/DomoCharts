--------------------------------------------------
-- Button  : 3 => Battery
-- Author  : Lazer
-- Version : 5.0
-- Date    : September 2015
--------------------------------------------------

-- System variables
local debug = true
local selfID = fibaro:getSelfId()
local ip = fibaro:get(selfID, 'IPAddress')
local port = fibaro:get(selfID, 'TCPPort')
local NAS = Net.FHttp(ip, tonumber(port))
local erreur = 0

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
		local batteries = {}
		jsonTable = json.decode(response)
		for i = 1, #jsonTable do
			if version == 4 then
				-- Keep only parent devices
				-- Notes : Z-Wave devices have parentId=1
				--         Plugins (like Netatmo or Krikroff's Virtual Sensor) have parentId=0
				if jsonTable[i].parentId and jsonTable[i].parentId == 1 then
					-- Look for battery operated devices
					if jsonTable[i].interfaces then
						for j = 1, #jsonTable[i].interfaces do
							if jsonTable[i].interfaces[j] == "battery" then
								local batteryLevel = jsonTable[i].properties.batteryLevel
								if tonumber(batteryLevel) == 255 then batteryLevel = 0 end
								if tonumber(batteryLevel) > 100 then batteryLevel = 100 end
								if debug then
									fibaro:debug(jsonTable[i].id.." "..jsonTable[i].name.." "..batteryLevel.."%")
								end
								-- Prepare JSON data
								batteries[#batteries+1] = {}
								batteries[#batteries].id = jsonTable[i].id
								batteries[#batteries].date = os.date("%Y-%m-%d")
								batteries[#batteries].type = "battery"
								batteries[#batteries].value = batteryLevel
								break
							end
						end
					end
				end
			elseif version == 3 then
				-- Keep only parent devices
				if jsonTable[i].properties.parentID and jsonTable[i].properties.parentID == "1" then
					-- Look for battery operated devices
					if jsonTable[i].properties.isBatteryOperated and jsonTable[i].properties.isBatteryOperated == "1" then
						local batteryLevel = jsonTable[i].properties.batteryLevel
						if tonumber(batteryLevel) == 255 then batteryLevel = "0" end
						if tonumber(batteryLevel) > 100 then batteryLevel = "100" end
						if debug then
							fibaro:debug(jsonTable[i].id.." "..jsonTable[i].name.." "..batteryLevel.."%")
						end
						-- Prepare JSON data
						batteries[#batteries+1] = {}
						batteries[#batteries].id = jsonTable[i].id
						batteries[#batteries].date = os.date("%Y-%m-%d")
						batteries[#batteries].type = "battery"
						batteries[#batteries].value = batteryLevel
					end
				end
			end
		end

		-- Send data to NAS (SQL DB)
		if debug then
			fibaro:debug(json.encode(batteries))
		end
		payload = "/graph/data_post.php"
		response, status, errorCode = NAS:POST(payload, json.encode(batteries))
		if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
			jsonTable2 = json.decode(response);
			if jsonTable2.success == true then
				fibaro:debug('<span style="display:inline;color:green;">OK : '..(jsonTable2.rowcount or "???")..' lines inserted in DB</span>')
			else
				erreur = erreur + 1
				fibaro:debug('<span style="display:inline;color:red;">Error '..(jsonTable2.error and jsonTable2.error.code or "???")..' : '..(jsonTable2.error and jsonTable2.error.message or "???")..'</span>')
			end
		else
			erreur = erreur + 1
			fibaro:debug('<span style="display:inline;color:red;">Error : Can not connect to NAS, errorCode='..errorCode..', status='..status..', payload='..payload..', response='..(response or "")..'</span>')
		end

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
	fibaro:log("Batteries uploaded")
end
