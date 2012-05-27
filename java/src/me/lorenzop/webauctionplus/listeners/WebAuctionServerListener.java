package me.lorenzop.webauctionplus.listeners;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import net.milkbowl.vault.economy.Economy;
import org.bukkit.event.EventHandler;
import org.bukkit.event.EventPriority;
import org.bukkit.event.Listener;
import org.bukkit.event.server.PluginEnableEvent;
import org.bukkit.plugin.RegisteredServiceProvider;

public class WebAuctionServerListener implements Listener {

	private final WebAuctionPlus plugin;

	public WebAuctionServerListener(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPluginEnable(PluginEnableEvent event) {
//		// setup permissions
//		if (plugin.permission == null) {
//			RegisteredServiceProvider<Permission> permissionProvider = plugin.getServer().getServicesManager().getRegistration(Permission.class);
//			if (permissionProvider != null) {
//				plugin.permission = (Permission)permissionProvider.getProvider();
//				plugin.log.info(plugin.logPrefix + "Permission method enabled: " + plugin.permission.getName());
//			}
//			return;
//		}
		// setup economy
		if (plugin.economy == null) {
			RegisteredServiceProvider<Economy> economyProvider = plugin.getServer().getServicesManager().getRegistration(net.milkbowl.vault.economy.Economy.class);
			if (economyProvider != null) {
				plugin.economy = (Economy)economyProvider.getProvider();
				WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Payment method enabled: " + plugin.economy.getName());
			}
			return;
		}
	}

}