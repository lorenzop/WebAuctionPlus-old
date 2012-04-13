package me.exote.webauctionplus.listeners;

import me.lorenzop.webauctionplus.WebAuctionPlus;

import org.bukkit.event.EventHandler;
import org.bukkit.event.EventPriority;
import org.bukkit.event.Listener;
import org.bukkit.event.server.PluginEnableEvent;

public class WebAuctionServerListener implements Listener {
	private WebAuctionPlus plugin;

	public WebAuctionServerListener(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPluginEnable(PluginEnableEvent event) {
		if (plugin.economy != null) {
			if (plugin.economy.isEnabled()) {
				plugin.log.info(plugin.logPrefix + "Payment method enabled: " + plugin.economy.getName());
			}
		}
		if (plugin.permission != null) {
			if (plugin.permission.isEnabled()) {
				plugin.log.info(plugin.logPrefix + "Permission method enabled: " + plugin.permission.getName());
			}
		}
	}

}