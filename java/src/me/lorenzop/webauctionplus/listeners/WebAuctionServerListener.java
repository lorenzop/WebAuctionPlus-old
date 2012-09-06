package me.lorenzop.webauctionplus.listeners;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import net.milkbowl.vault.economy.Economy;

import org.bukkit.Bukkit;
import org.bukkit.event.EventHandler;
import org.bukkit.event.EventPriority;
import org.bukkit.event.Listener;
import org.bukkit.event.server.PluginEnableEvent;
import org.bukkit.plugin.RegisteredServiceProvider;

public class WebAuctionServerListener implements Listener {


	public WebAuctionServerListener() {
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPluginEnable(PluginEnableEvent event) {
		// setup economy
		if (WebAuctionPlus.vaultEconomy == null) {
			RegisteredServiceProvider<Economy> economyProvider = Bukkit.getServer().getServicesManager().getRegistration(net.milkbowl.vault.economy.Economy.class);
			if (economyProvider != null) {
				WebAuctionPlus.vaultEconomy = (Economy)economyProvider.getProvider();
				WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Payment method enabled: " + WebAuctionPlus.vaultEconomy.getName());
			}
			return;
		}
	}

}