--------------------------------------------------
-- Button  : 1 => Sensors : Temperature, Humidity, Light, ...
-- Author  : Lazer
-- Version : 5.0
-- Date    : September 2015
--------------------------------------------------

-- User Global Variables
local variables = {}

-- System variables
local debug = false
local selfID = fibaro:getSelfId()
local ip = fibaro:get(selfID, 'IPAddress')
local port = fibaro:get(selfID, 'TCPPort')
local NAS = Net.FHttp(ip, tonumber(port))
local erreur = 0
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
	for i = 1, #sensors do
		payload = "/api/devices?type=" .. sensors[i][version]
		response, status, errorCode = HC2:GET(payload)
		if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then

			-- Get data
			local datas = {}
			jsonTable = json.decode(response)
			for j = 1, #jsonTable do
				-- Exclude hidden, disabled, and dead devices
				if jsonTable[j].visible and jsonTable[j].visible == true and jsonTable[j].enabled and jsonTable[j].enabled == true and jsonTable[j].properties.dead and jsonTable[j].properties.dead == "false" then
					datas[#datas+1] = {}
					datas[#datas].id = jsonTable[j].id
					datas[#datas].timestamp = 'NULL'
					datas[#datas].type = sensors[i].type
					datas[#datas].value = jsonTable[j].properties.value
				elseif debug then
					fibaro:debug("Device "..jsonTable[j].id.." "..jsonTable[j].name.." excluded")
				end
			end

			-- Send data to NAS
			SendDataNAS(datas)

		else
			erreur = erreur + 1
			fibaro:debug('<span style="display:inline;color:red;">status='..status..', errorCode='..errorCode..', payload='..payload..', response='..(response or "")..'</span>')
		end
	end

	-- Get HC2 Netatmo Plugin Device
	if version == 4 then
		payload = "/api/devices?type=com.fibaro.netatmoWeatherStation"
		response, status, errorCode = HC2:GET(payload)
		if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then

			-- Get data
			jsonTable = json.decode(response)
			if jsonTable[1] and jsonTable[1].properties and jsonTable[1].properties.childTable and jsonTable[1].properties.childTable ~= "" then
				local datas = {}
				local childTable = json.decode(jsonTable[1].properties.childTable)
				for id, data in pairs(childTable) do
					local sensor = data:match("%.([^%.]+)") -- Split string after dot
					for k,v in pairs(netatmo) do
						if sensor == k then
							-- Get sensor
							local value = fibaro:getValue(id, "value")
							if debug then
								fibaro:debug(id..' '..fibaro:getName(id)..' : '..value)
							end
							datas[#datas+1] = {}
							datas[#datas].id = id
							datas[#datas].timestamp = 'NULL'
							datas[#datas].type = v
							datas[#datas].value = value
							break
						end
					end
				end

				-- Send data to NAS
				SendDataNAS(datas)

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
	fibaro:debug('<span style="display:inline;color:red;">status='..status..', errorCode='..errorCode..', payload='..payload..', response='..(response or "")..'</span>')
end

-- Get Meteo
local datas = {}
datas[1] = {}
datas[1].id = 3
datas[1].timestamp = 'NULL'
datas[1].type = "temperature"
datas[1].value = fibaro:getValue(3, "Temperature")
datas[2] = {}
datas[2].id = 3
datas[2].timestamp = 'NULL'
datas[2].type = "humidity"
datas[2].value = fibaro:getValue(3, "Humidity")
datas[3] = {}
datas[3].id = 3
datas[3].timestamp = 'NULL'
datas[3].type = "wind"
datas[3].value = fibaro:getValue(3, "Wind")
-- Send data to NAS
SendDataNAS(datas)

-- Get User Variable list (updated from FHEM through API)
datas = nil
local datas = {}
for i = 1, #variables do
	payload = "/graph/data_post_" .. variables[i].type .. ".php?id=" .. variables[i].id .. "&value=" .. fibaro:getGlobalValue(variables[i].name)
	datas[#datas+1] = {}
	datas[#datas].id = variables[i].id
	datas[#datas].timestamp = 'NULL'
	datas[#datas].type = variables[i].type
	datas[#datas].value = fibaro:getGlobalValue(variables[i].name)
end
-- Send data to NAS
SendDataNAS(datas)

if erreur > 0 then
	fibaro:log("Erreur")
else
	fibaro:log("Sensors uploaded")
end
