--[[
%% properties
%% autostart
%% globals
--]]

local VirtualDeviceID = 82

local seconde = os.date('%S')
if tonumber(seconde) ~= 0 then
	local delta = 60 - seconde
	fibaro:debug("Time is  " .. os.date('%H:%M:%S') .. ", waiting for " .. delta .. " seconds...")
	fibaro:sleep(delta*1000)
end

local hour = os.date('%H')
local day = os.date('%d')

while true do

	-- Actions to perform every new minute
	fibaro:call(VirtualDeviceID, "pressButton", "1"); -- Sensors (Temperature, humidity, light)
	fibaro:call(VirtualDeviceID, "pressButton", "2"); -- Power consumption
	--fibaro:call(17, "pressButton", "1"); -- Eco-Devices Teleinfo

	-- Actions to perform every new hour
	local newhour = os.date('%H')
	if newhour ~= hour then
		fibaro:debug('New hour')
		-- Actions to perform at 23:000
		if tonumber(newhour) == 23 then
			fibaro:call(VirtualDeviceID, "pressButton", "3"); -- Battery level
		end
		hour = newhour
	end

	-- Actions to perform every new day
	local newday = os.date('%d')
	if newday ~= day and tonumber(os.date('%M')) >= 1 then
		fibaro:debug('New day')
		fibaro:call(VirtualDeviceID, "pressButton", "4"); -- Devices
		fibaro:call(VirtualDeviceID, "pressButton", "5"); -- Trend data
		fibaro:call(VirtualDeviceID, "pressButton", "6"); -- Energy
		--fibaro:call(81, "pressButton", "1"); -- Water
		day = newday
	end

	fibaro:debug('Last run : ' .. os.date('%d/%m/%Y %H:%M:%S'))
	fibaro:sleep(60000); -- 1 minute

end
