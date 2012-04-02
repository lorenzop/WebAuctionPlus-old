package me.exote.webauction.listeners;

import me.exote.webauction.WebAuction;

import org.bukkit.event.EventHandler;
import org.bukkit.event.EventPriority;
import org.bukkit.event.Listener;
import org.bukkit.event.server.PluginEnableEvent;

public class WebAuctionServerListener implements Listener {
	private WebAuction plugin;

	public WebAuctionServerListener(WebAuction webAuction) {
		this.plugin = webAuction;
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPluginEnable(PluginEnableEvent event) {
		if (plugin.economy != null) {
			if (plugin.economy.isEnabled()) {
				System.out.println(plugin.logPrefix + " Payment method enabled: " + plugin.economy.getName());
			}
		}
		if (plugin.permission != null) {
			if (plugin.permission.isEnabled()) {
				System.out.println(plugin.logPrefix + " Permission method enabled: " + plugin.permission.getName());
			}
		}
	}

}