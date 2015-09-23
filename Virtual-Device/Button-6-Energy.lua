--------------------------------------------------
-- Button  : 6 => Energy
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
local devices = {}

-- Get HC2 Device list
local HC2 = Net.FHttp("127.0.0.1", 11111)
response, status, errorCode = HC2:GET("/api/devices")
if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
	jsonTable = json.decode(response)
	for i, device in pairs(jsonTable) do
		if device.visible and device.visible == true and device.enabled and device.enabled == true and device.properties.dead and device.properties.dead == "false" then
			if device.properties.showEnergy then
				if debug then
					fibaro:debug("device => "..device.id.." "..device.name.." "..device.properties.power.."W")
				end
				table.insert(devices, device.id)
			end
		end
	end
else
	erreur = erreur + 1
	fibaro:debug('<span style="color:red;">Error : Can not connect to HC2, errorCode='..errorCode..', status='..status..', response='..(response or "")..'</span>')
end
local device = table.concat(devices, ',')
if debug then
	fibaro:debug(device)
end

-- Get teleinfo data from NAS (SQL DB)
local payload = "/graph/teleinfo_energy_get.php"
response, status, errorCode = NAS:GET(payload)
if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
	if debug then
		fibaro:debug(response)
	end
	jsonTable = json.decode(response)
	if jsonTable.success and jsonTable.success == true then
		if jsonTable.data then
			for jour, data in pairs(jsonTable.data) do -- using pairs() the order that items are returned is not defined, not even for indexed tables
				fibaro:debug(jour)
				-- On construit un tableau contenant le timestamp de début et de fin pour chaque plage horaire de tarification
				local timestamps = {}
				local j = 0
				for i = 1, #data do
					if debug then
						fibaro:debug("data => "..i..' - '..data[i][1]..' : '..data[i][2])
					end
					timestamps[i] = {}
					timestamps[i][0] = data[i][2] -- tarif
					timestamps[i][1] = data[i][1] -- timestamp start
					if i > 1 then
						timestamps[i-1][2] = data[i][1] -- timestamp end
					end
					j = i
				end
				timestamps[j][2] = os.time({year=os.date("%Y", timestamps[1][1]), month=os.date("%m", timestamps[1][1]), day=os.date("%d", timestamps[1][1])+1, hour=os.date("%H", timestamps[1][1]), min=os.date("%M", timestamps[1][1]), sec=os.date("%S", timestamps[1][1])}) -- timestamp end of day

				-- Get energy consumption for every devices in HC2
				local energy = {}
				for i = 1, #timestamps do
					if debug then
						fibaro:debug("timestamps => i = "..i.." - "..timestamps[i][0].." - "..timestamps[i][1].." "..timestamps[i][2])
					end
					payload = '/api/energy/'..timestamps[i][1]..'/'..timestamps[i][2]..'/compare/devices/power/'..device;
					--fibaro:debug(payload)
					response, status, errorCode = HC2:GET(payload)
					if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
						--fibaro:debug(response)
						jsonTable2 = json.decode(response)
						--table.sort(jsonTable2, function(a,b) return a.id<b.id end) -- Sort results by id, because HC2 API seems to sort results by consumption
						for j = 1, #jsonTable2 do
							if not energy[jsonTable2[j].id] then
								energy[jsonTable2[j].id] = {}
							end
							if debug then
								fibaro:debug(jsonTable2[j].id.." "..jsonTable2[j].kWh)
							end
							energy[jsonTable2[j].id][timestamps[i][0]] = (energy[jsonTable2[j].id][timestamps[i][0]] or 0) + jsonTable2[j].kWh
						end
					else
						erreur = erreur + 1
						fibaro:debug('<span style="color:red;">Error : Can not connect to HC2, errorCode='..errorCode..', status='..status..', payload='..payload..', response='..(response or "")..'</span>')
					end
				end

				-- Prepare JSON data
				local energies = {}
				for id, toto in pairs(energy) do
					energies[#energies+1] = {}
					energies[#energies].id = id
					energies[#energies].date = jour
					energies[#energies].type = "energy"
					for tarif, kwh in pairs(toto) do
						if debug then
							fibaro:debug("energy => id = "..id.." - tarif = "..tarif.." - kwh = "..kwh)
						end
						energies[#energies]["value_"..tarif] = kwh
					end
				end
				table.sort(energies, function(a,b) return a.id<b.id end) -- Sort results by id
				if debug then
					fibaro:debug(json.encode(energies))
				end

				-- Send energy data to NAS (SQL DB)
				payload = "/graph/data_post.php"
				response, status, errorCode = NAS:POST(payload, json.encode(energies))
				if tonumber(errorCode) == 0 and tonumber(status) == 200 and response ~= nil and response ~= "" then
					jsonTable2 = json.decode(response);
					if jsonTable2.success == true then
						fibaro:debug('<span style="color:green;">OK : '..(jsonTable2.rowcount or "???")..' lines inserted in DB</span>')
					else
						erreur = erreur + 1
						fibaro:debug('<span style="color:red;">Error '..(jsonTable2.error and jsonTable2.error.code or "???")..' : '..(jsonTable2.error and jsonTable2.error.message or "???")..'</span>')
					end
				else
					erreur = erreur + 1
					fibaro:debug('<span style="color:red;">Error : Can not connect to NAS, errorCode='..errorCode..', status='..status..', payload='..payload..', response='..(response or "")..'</span>')
				end

			end
		else
			erreur = erreur + 1
			fibaro:debug('<span style="color:red;">Error : Missing data from NAS : '..payload..'</span>')
		end
	else
		erreur = erreur + 1
		fibaro:debug('<span style="color:red;">Error '..(jsonTable.error and jsonTable.error.code or "???")..' : '..(jsonTable.error and jsonTable.error.message or "???")..'</span>')
	end
else
	erreur = erreur + 1
	fibaro:debug('<span style="color:red;">Error : Can not connect to NAS, errorCode='..errorCode..', status='..status..', payload='..payload..', response='..(response or "")..'</span>')
end

if erreur > 0 then
	fibaro:log("Erreur")
else
	fibaro:log("Consumption uploaded")
end
