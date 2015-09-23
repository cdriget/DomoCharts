--------------------------------------------------
-- Button  : 4 => Devices
-- Author  : Lazer
-- Version : 5.0
-- Date    : September 2015
--------------------------------------------------

-- User Global Variables
local variables = {}

-- System variables
local debug = true
local selfID = fibaro:getSelfId()
local ip = fibaro:get(selfID, 'IPAddress')
local port = fibaro:get(selfID, 'TCPPort')
local NAS = Net.FHttp(ip, tonumber(port))
local erreur = 0
local datas = {}
local sensors = {
	{
		["type"] = "temperature",
		[3] = "temperature_sensor",
		[4] = "com.fibaro.temperatureSensor"
	},
	{
		["type"] = "humidity",
		[3] = "humidity_sensor",
		[4] = "com.fibaro.humiditySensor"
	},
	{
		["type"] = "temperature",
		[3] = "thermostat_setpoint",
		[4] = "com.fibaro.setPoint"
	},
	{
		["type"] = "temperature",
		[3] = "thermostat_setpoint",
		[4] = "com.fibaro.thermostatHorstmann"
	},
	{
		["type"] = "light",
		[3] = "light_sensor",
		[4] = "com.fibaro.lightSensor"
	}
}
local netatmo = {
	["CO2"] = "co2",
	["Press"] = "pressure",
	["Noise"] = "noise",
	["Rain"] = "rain"
}

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
		jsonTable = json.decode(response)
		for i = 1, #jsonTable do
			if version == 4 then
				if jsonTable[i].interfaces then
					for j = 1, #jsonTable[i].interfaces do
						-- Look for devices returning power consumption
						if jsonTable[i].interfaces[j] == "power" then
							local deviceName = jsonTable[i].name
							local roomID = jsonTable[i].roomID
							local roomName = fibaro:getRoomNameByDeviceID(jsonTable[i].id)
							if debug then
								fibaro:debug(i.." "..jsonTable[i].id.." "..jsonTable[i].name.." "..roomName)
							end
							datas[#datas+1] = {}
							datas[#datas].id = jsonTable[i].id
							datas[#datas].type = 'power'
							datas[#datas].name = deviceName
							datas[#datas].roomid = roomID
							datas[#datas].roomname = roomName
							datas[#datas+1] = {}
							datas[#datas].id = jsonTable[i].id
							datas[#datas].type = 'energy'
							datas[#datas].name = deviceName
							datas[#datas].roomid = roomID
							datas[#datas].roomname = roomName
						end
						-- Look for battery operated devices
						if jsonTable[i].interfaces[j] == "battery" then
							-- Keep only parent devices
							if jsonTable[i].parentId and jsonTable[i].parentId == 1 then
								local deviceName = jsonTable[i].name
								local roomID = jsonTable[i].roomID
								local roomName = fibaro:getRoomNameByDeviceID(jsonTable[i].id)
								if debug then
									fibaro:debug(i.." "..jsonTable[i].id.." "..jsonTable[i].name.." "..roomName)
								end
								datas[#datas+1] = {}
								datas[#datas].id = jsonTable[i].id
								datas[#datas].type = 'battery'
								datas[#datas].name = deviceName
								datas[#datas].roomid = roomID
								datas[#datas].roomname = roomName
							end
						end
					end
				end
				-- Look for sensors devices returning environmental values
				for j = 1, #sensors do
					if jsonTable[i].type == sensors[j][version] then
						local deviceName = jsonTable[i].name
						local roomID = jsonTable[i].roomID
						local roomName = fibaro:getRoomNameByDeviceID(jsonTable[i].id)
						if debug then
							fibaro:debug(i.." "..jsonTable[i].id.." "..deviceName.." "..roomName)
						end
						datas[#datas+1] = {}
						datas[#datas].id = jsonTable[i].id
						datas[#datas].type = sensors[j].type
						datas[#datas].name = deviceName
						datas[#datas].roomid = roomID
						datas[#datas].roomname = roomName
					end
				end
			elseif version == 3 then
				-- Look for devices returning power consumption
				if jsonTable[i].properties.unitSensor and jsonTable[i].properties.unitSensor == "W" then
					local deviceName = jsonTable[i].name
					local roomID = jsonTable[i].roomID
					local roomName = fibaro:getRoomNameByDeviceID(jsonTable[i].id)
					if debug then
						fibaro:debug(i.." "..jsonTable[i].id.." "..jsonTable[i].name.." "..roomName)
					end
					datas[#datas+1] = {}
					datas[#datas].id = jsonTable[i].id
					datas[#datas].type = 'power'
					datas[#datas].name = deviceName
					datas[#datas].roomid = roomID
					datas[#datas].roomname = roomName
					datas[#datas+1] = {}
					datas[#datas].id = jsonTable[i].id
					datas[#datas].type = 'energy'
					datas[#datas].name = deviceName
					datas[#datas].roomid = roomID
					datas[#datas].roomname = roomName
				end
				-- Look for battery operated devices
				if jsonTable[i].properties.isBatteryOperated and jsonTable[i].properties.isBatteryOperated == "1" then
					-- Keep only parent devices
					if jsonTable[i].properties.parentID and jsonTable[i].properties.parentID == "1" then
						local deviceName = jsonTable[i].name
						local roomID = jsonTable[i].roomID
						local roomName = fibaro:getRoomNameByDeviceID(jsonTable[i].id)
						if debug then
							fibaro:debug(i.." "..jsonTable[i].id.." "..jsonTable[i].name.." "..roomName)
						end
						datas[#datas+1] = {}
						datas[#datas].id = jsonTable[i].id
						datas[#datas].type = 'battery'
						datas[#datas].name = deviceName
						datas[#datas].roomid = roomID
						datas[#datas].roomname = roomName
					end
				end
				-- Look for sensors devices returning environmental values
				for j = 1, #sensors do
					if jsonTable[i].type == sensors[j][version] then
						local deviceName = jsonTable[i].name
						local roomID = jsonTable[i].roomID
						local roomName = fibaro:getRoomNameByDeviceID(jsonTable[i].id)
						if debug then
							fibaro:debug(i.." "..jsonTable[i].id.." "..jsonTable[i].name.." "..roomName)
						end
						datas[#datas+1] = {}
						datas[#datas].id = jsonTable[i].id
						datas[#datas].type = sensors[j].type
						datas[#datas].name = deviceName
						datas[#datas].roomid = roomID
						datas[#datas].roomname = roomName
					end
				end
			end
		end
	else
		erreur = erreur + 1
		fibaro:debug('<span style="color:red;">status='..status..', errorCode='..errorCode..', payload='..payload..', response='..(response or "")..'</span>')
	end

	-- Get HC2 Netatmo Weather Station plugin device
	if version == 4 then
		payload = "/api/devices?type=com.fibaro.netatmoWeatherStation"
		response, status, errorCode = HC2:GET(payload)
		if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
			-- Get data
			jsonTable = json.decode(response)
			if jsonTable[1] and jsonTable[1].properties and jsonTable[1].properties.childTable and jsonTable[1].properties.childTable ~= "" then
				local childTable = json.decode(jsonTable[1].properties.childTable)
				for id, data in pairs(childTable) do
					local sensor = data:match("%.([^%.]+)") -- Split string after dot
					for k,v in pairs(netatmo) do
						if sensor == k then
							local deviceName = fibaro:getName(id)
							local roomID = fibaro:getRoomID(id)
							local roomName = fibaro:getRoomNameByDeviceID(id)
							if debug then
								fibaro:debug(id.." "..deviceName.." "..roomName)
							end
							datas[#datas+1] = {}
							datas[#datas].id = id
							datas[#datas].type = v
							datas[#datas].name = deviceName
							datas[#datas].roomid = roomID
							datas[#datas].roomname = roomName
							break
						end
					end
				end
			elseif debug then
				fibaro:debug('<span style="display:inline;color:red;">No Netatmo device found')
			end
		else
			erreur = erreur + 1
			fibaro:debug('<span style="display:inline;color:red;">status='..status..', errorCode='..errorCode..', payload='..payload..', response='..(response or "")..'</span>')
		end
	elseif debug then
		fibaro:debug('<span style="display:inline;color:red;">Netatmo plugin not supported')
	end

else
	erreur = erreur + 1
	fibaro:debug('<span style="color:red;">status='..status..', errorCode='..errorCode..', payload='..payload..', response='..(response or "")..'</span>')
end

-- Get Weather devices
--local deviceName = fibaro:getName(3)
local deviceName = 'Météo'
if debug then
	fibaro:debug("1 3 "..deviceName)
end
datas[#datas+1] = {}
datas[#datas].id = 3
datas[#datas].type = 'temperature'
datas[#datas].name = deviceName
datas[#datas].roomid = 0
datas[#datas].roomname = ''
if debug then
	fibaro:debug("2 3 "..deviceName)
end
datas[#datas+1] = {}
datas[#datas].id = 3
datas[#datas].type = 'humidity'
datas[#datas].name = deviceName
datas[#datas].roomid = 0
datas[#datas].roomname = ''
if debug then
	fibaro:debug("3 3 "..deviceName)
end
datas[#datas+1] = {}
datas[#datas].id = 3
datas[#datas].type = 'wind'
datas[#datas].name = deviceName
datas[#datas].roomid = 0
datas[#datas].roomname = ''

-- Get User Variable list (From FHEM)
for i = 1, #variables do
	local roomName = fibaro:getRoomName(variables[i].room)
	if debug then
		fibaro:debug(i.." "..variables[i].id.." "..variables[i].name.." "..roomName)
	end
	datas[#datas+1] = {}
	datas[#datas].id = variables[i].id
	datas[#datas].type = variables[i].type
	datas[#datas].name = variables[i].name
	datas[#datas].roomid = variables[i].room
	datas[#datas].roomname = roomName
end

-- Send data to NAS (SQL DB)
if debug then
	fibaro:debug(json.encode(datas))
end
payload = "/graph/device_post.php"
response, status, errorCode = NAS:POST(payload, json.encode(datas))
if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
	jsonTable2 = json.decode(response)
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

if erreur > 0 then
	fibaro:log("Erreur")
else
	fibaro:log("Devices uploaded")
end
