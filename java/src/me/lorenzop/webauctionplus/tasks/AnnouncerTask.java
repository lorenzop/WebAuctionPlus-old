package me.lorenzop.webauctionplus.tasks;

import org.bukkit.Server;
import me.exote.webauctionplus.WebAuctionPlus;

public class AnnouncerTask implements Runnable {

	public int currentAnnouncement   = 0;
	public int numberOfAnnouncements = 0;

	private final WebAuctionPlus plugin;

	public AnnouncerTask(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public void run() {
		if (plugin.announcementMessages.isEmpty()) return;
		// random
		if (plugin.announceRandom) {
			currentAnnouncement = plugin.getNewRandom(currentAnnouncement, plugin.announcementMessages.size() - 1);
			announce(currentAnnouncement);
		// sequential
		} else {
			while (currentAnnouncement > plugin.announcementMessages.size()-1) {
				currentAnnouncement -= plugin.announcementMessages.size();
			}
			announce(currentAnnouncement);
			currentAnnouncement++;
		}
		numberOfAnnouncements++;
	}

	public void announce(int lineNumber){
		if (plugin.announcementMessages.isEmpty() || lineNumber < 0) return;
		plugin.log.info(plugin.logPrefix + "Announcement # " + Integer.toString(lineNumber));
		announce(plugin.announcementMessages.get(lineNumber));
	}

	public void announce(String line){
		if (line.isEmpty()) return;
		Server server = plugin.getServer();
		String[] messages = line.split("&n");
		for (String message : messages) {
			// is command
			if (message.startsWith("/")) {
				server.dispatchCommand(server.getConsoleSender(), message.substring(1));
			} else if (server.getOnlinePlayers().length > 0) {
				message = plugin.ReplaceColors(plugin.announcementPrefix + message);
				server.broadcast(message, "wa.announcer.receive");
			}
		}
	}

}