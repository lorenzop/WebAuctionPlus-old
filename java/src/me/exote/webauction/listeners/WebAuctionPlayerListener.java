package me.exote.webauction.listeners;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Random;

import me.exote.webauction.WebAuction;
import me.exote.webauction.dao.AuctionItem;
import me.exote.webauction.dao.AuctionMail;
import me.exote.webauction.dao.AuctionPlayer;
import me.exote.webauction.dao.SaleAlert;

import org.bukkit.Material;
import org.bukkit.block.Block;
import org.bukkit.block.Sign;
import org.bukkit.enchantments.Enchantment;
import org.bukkit.event.block.Action;
import org.bukkit.event.player.PlayerInteractEvent;
import org.bukkit.event.player.PlayerJoinEvent;
import org.bukkit.event.player.PlayerListener;
import org.bukkit.event.player.PlayerQuitEvent;
import org.bukkit.inventory.ItemStack;

public class WebAuctionPlayerListener extends PlayerListener {

	private final WebAuction plugin;

	public WebAuctionPlayerListener(WebAuction plugin) {
		this.plugin = plugin;
	}

	public static double round(double unrounded, int precision, int roundingMode) {
		BigDecimal bd = new BigDecimal(unrounded);
		BigDecimal rounded = bd.setScale(precision, roundingMode);
		return rounded.doubleValue();
	}

	@Override
	public void onPlayerQuit(PlayerQuitEvent event){
		plugin.lastSignUse.remove(event.getPlayer().getName());
	}
	
	@Override
	public void onPlayerJoin(PlayerJoinEvent event) {
		String player = event.getPlayer().getName();

		// Alert player of any new sale alerts
		if (plugin.showSalesOnJoin == true){
			List<SaleAlert> saleAlerts = plugin.dataQueries.getNewSaleAlertsForSeller(player);
			for (SaleAlert saleAlert : saleAlerts) {
				event.getPlayer().sendMessage(plugin.logPrefix + "You sold " + saleAlert.getQuantity() + " " + saleAlert.getItem() + " to " + saleAlert.getBuyer() + " for "+ saleAlert.getPriceEach() + " each.");
				plugin.dataQueries.markSaleAlertSeen(saleAlert.getId());
			}
		}

		// Alert player of any new mail
		if (plugin.dataQueries.hasMail(player)) {
			event.getPlayer().sendMessage(plugin.logPrefix + "You have new mail!");
		}

		// Determine permissions
		int canBuy = 0;
		int canSell = 0;
		int isAdmin = 0;
		if (plugin.permission.has(event.getPlayer(), "wa.canbuy")) {
			canBuy = 1;
		}
		if (plugin.permission.has(event.getPlayer(), "wa.cansell")) {
			canSell = 1;
		}
		if (plugin.permission.has(event.getPlayer(), "wa.webadmin")) {
			isAdmin = 1;
		}

		if (null != plugin.dataQueries.getPlayer(player)) {
			plugin.log.info(plugin.logPrefix + "Player found - "+ player+ " with permissions: canbuy = " + canBuy + " cansell = " + canSell + " isAdmin = " + isAdmin);
			// Update permissions
			plugin.dataQueries.updatePlayerPermissions(player, canBuy, canSell, isAdmin);
		}
	}

	@SuppressWarnings("deprecation")
	public void onPlayerInteract(PlayerInteractEvent event) {
		if (event.getAction() != Action.RIGHT_CLICK_BLOCK) {
			return;
		}

		Block block = event.getClickedBlock();
		if (null == block || (block.getType() != Material.SIGN_POST && block.getType() != Material.WALL_SIGN)) {
			return;
		}

		// it's a sign
		Sign sign = (Sign) block.getState();
		String[] lines = sign.getLines();

		if (!lines[0].equals("[WebAuction]")) {
			return;
		}

		String player = event.getPlayer().getName();
		event.setCancelled(true);

		// Make sure we can use the sign
		if (plugin.lastSignUse.containsKey(player)) {
			long lastSignUse = plugin.lastSignUse.get(player);
			if (lastSignUse + plugin.signDelay > plugin.getCurrentMilli()) {
				event.getPlayer().sendMessage(plugin.logPrefix + "Please wait a bit before using that again");
				return;
			}
		}
		plugin.lastSignUse.put(player, plugin.getCurrentMilli());

		if (lines[1].equals("Deposit")) {
			if (plugin.permission.has(event.getPlayer(), "wa.use.deposit.money")) {
				double amount = 0.0;
				if (!lines[2].equals("All")) {
					amount = Double.parseDouble(lines[2]);
				}
				if (plugin.economy.has(player, amount)) {
					AuctionPlayer auctionPlayer = plugin.dataQueries.getPlayer(player);
					if (null != auctionPlayer) {
						double currentMoney = auctionPlayer.getMoney();
						if (lines[2].equals("All")) {
							amount = plugin.economy.getBalance(player);
						}
						currentMoney += amount;
						currentMoney = round(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
						event.getPlayer().sendMessage(plugin.logPrefix + "Added " + amount + " to auction account, new auction balance: " + currentMoney);
						plugin.dataQueries.updatePlayerMoney(player, currentMoney);
						plugin.economy.withdrawPlayer(player, amount);
					} else {
						event.getPlayer().sendMessage(plugin.logPrefix + "No WebAuction account found, try logging off and back on again");
					}
				} else {
					event.getPlayer().sendMessage(plugin.logPrefix + "You do not have enough money in your pocket.");
				}
			}
		} else if (lines[1].equals("Shout")) {
			Random generator = new Random();
			int roll = generator.nextInt(20);
			switch (roll){
			case 0:
				event.getPlayer().sendMessage(plugin.logPrefix + "RAAN MIR TAH!");
				break;
			case 1:
				event.getPlayer().sendMessage(plugin.logPrefix + "LAAS YAH NIR!");
				break;
			case 2:
				event.getPlayer().sendMessage(plugin.logPrefix + "FEIM ZII GRON!");
				break;
			case 3:
				event.getPlayer().sendMessage(plugin.logPrefix + "OD AH VIING!");
				break;
			case 4:
				event.getPlayer().sendMessage(plugin.logPrefix + "HUN KAL ZOOR!");
				break;
			case 5:
				event.getPlayer().sendMessage(plugin.logPrefix + "LOK VAH KOOR!");
				break;
			case 6:
				event.getPlayer().sendMessage(plugin.logPrefix + "ZUN HAAL VIK!");
				break;
			case 7:
				event.getPlayer().sendMessage(plugin.logPrefix + "FAAS RU MAAR!");
				break;
			case 8:
				event.getPlayer().sendMessage(plugin.logPrefix + "JOOR ZAH FRUL!");
				break;
			case 9:
				event.getPlayer().sendMessage(plugin.logPrefix + "SU GRAH DUN!");
				break;
			case 10:
				event.getPlayer().sendMessage(plugin.logPrefix + "YOL TOOR SHOL!");
				break;
			case 11:
				event.getPlayer().sendMessage(plugin.logPrefix + "FO KRAH DIIN!");
				break;
			case 12:
				event.getPlayer().sendMessage(plugin.logPrefix + "LIZ SLEN NUS!");
				break;
			case 13:
				event.getPlayer().sendMessage(plugin.logPrefix + "KAAN DREM OV!");
				break;
			case 14:
				event.getPlayer().sendMessage(plugin.logPrefix + "KRII LUN AUS!");
				break;
			case 15:
				event.getPlayer().sendMessage(plugin.logPrefix + "TIID KLO UL!");
				break;
			case 16:
				event.getPlayer().sendMessage(plugin.logPrefix + "STRUN BAH QO!");
				break;
			case 17:
				event.getPlayer().sendMessage(plugin.logPrefix + "ZUL MEY GUT!");
				break;
			case 18:
				event.getPlayer().sendMessage(plugin.logPrefix + "WULK NAH KEST!");
				break;
			default:
				event.getPlayer().sendMessage(plugin.logPrefix + "FUS RO DAH!");
				break;
			}	
		} else if (lines[1].equals("Withdraw")) {
			if (plugin.permission.has(event.getPlayer(), "wa.use.withdraw.money")) {
				double amount = 0.0;
				if (!lines[2].equals("All")) {
					amount = Double.parseDouble(lines[2]);
				}
				try {
					AuctionPlayer auctionPlayer = plugin.dataQueries.getPlayer(player);
					if (null != auctionPlayer) {
						// Match found!
						double currentMoney = auctionPlayer.getMoney();
						if (lines[2].equals("All")) {
							amount = currentMoney;
						}
						if (currentMoney >= amount) {
							currentMoney -= amount;
							currentMoney = round(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
							event.getPlayer().sendMessage(plugin.logPrefix + "Removed " + amount + " from auction account, new auction balance: " + currentMoney);
							plugin.dataQueries.updatePlayerMoney(player, currentMoney);
							plugin.economy.depositPlayer(player, amount);
						} else {
							event.getPlayer().sendMessage(plugin.logPrefix + "You do not have enough money in your WebAuction account.");
						}
					} else {
						event.getPlayer().sendMessage(plugin.logPrefix + "No WebAuction account found, try logging off and back on again");
					}
				} catch (Exception e) {
					e.printStackTrace();
				}
			} else {
				event.getPlayer().sendMessage(plugin.logPrefix + "You do not have permission to withdraw money");
				event.setCancelled(true);
			}
		} else if ((lines[1].equals("MailBox")) || (lines[1].equals("Mailbox")) || (lines[1].equals("Mail Box"))) {
			if ((lines[2].equals("Deposit")) && (plugin.permission.has(event.getPlayer(), "wa.use.deposit.items"))) {
				ItemStack stack = event.getPlayer().getItemInHand();
				if (stack != null) {
					int itemID = stack.getTypeId();
					if (itemID != 0) {
						int itemDamage = 0;
						if (stack.getDurability() >= 0) {
							itemDamage = stack.getDurability();
						}
						Map<Enchantment, Integer> itemEnchantments = stack.getEnchantments();
						int quantityInt = stack.getAmount();

						List<AuctionItem> auctionItems = plugin.dataQueries.getItems(player, itemID, itemDamage, false);
						Boolean foundMatch = false;

						for (AuctionItem auctionItem : auctionItems) {
							int itemTableIdNumber = auctionItem.getId();
							List<Integer> enchantmentIds = new ArrayList<Integer>();
							List<Integer> enchantmentIdsStoredTemp;
							for (Map.Entry<Enchantment, Integer> entry : itemEnchantments.entrySet()) {
								Enchantment key = entry.getKey();
								String enchName = key.getName();
								// player.sendMessage(enchName);
								int enchId = key.getId();
								int level = entry.getValue();

								int enchTableId = -1;
								while (enchTableId == -1) {
									int dbEnchTableId = plugin.dataQueries.getEnchantTableID(enchId, level, enchName);
									if (dbEnchTableId == -1) {
										plugin.dataQueries.createEnchantment(enchName, enchId, level);
									} else {
										enchTableId = dbEnchTableId;
									}
								}
								enchantmentIds.add(enchTableId);
								// player.sendMessage(enchantmentIds.size()+" part1");
							}
							Collections.sort(enchantmentIds);

							enchantmentIdsStoredTemp = plugin.dataQueries.getEnchantIDsForLinks(itemID, 0);
							Collections.sort(enchantmentIdsStoredTemp);

							if (enchantmentIds.equals(enchantmentIdsStoredTemp)) {
								int currentQuantity = auctionItem.getQuantity();
								currentQuantity += quantityInt;
								plugin.dataQueries.updateItemQuantity(currentQuantity, itemTableIdNumber);
								foundMatch = true;
							} else if ((enchantmentIds.isEmpty()) && (enchantmentIdsStoredTemp.isEmpty())) {
								int currentQuantity = auctionItem.getQuantity();
								currentQuantity += quantityInt;
								plugin.dataQueries.updateItemQuantity(currentQuantity, itemTableIdNumber);
								foundMatch = true;
							}
						}
						if (foundMatch == false) {
							// Create item
							plugin.dataQueries.createItem(itemID, itemDamage, player, quantityInt);

							// Retrieve to get ID
							List<AuctionItem> newItems = plugin.dataQueries.getItems(player, itemID, itemDamage, true);
							int itemTableId = -1;
							if (!newItems.isEmpty()) {
								itemTableId = newItems.get(0).getId();
							}

							for (Map.Entry<Enchantment, Integer> entry : itemEnchantments.entrySet()) {
								Enchantment key = entry.getKey();
								String enchName = key.getName();
								int enchId = key.getId();
								Integer level = entry.getValue();
								// see if exists already
								int enchTableId = -1;
								while (enchTableId == -1) {
									int dbEnchTableId = plugin.dataQueries.getEnchantTableID(enchId, level, enchName);
									if (dbEnchTableId == -1) {
										plugin.dataQueries.createEnchantment(enchName, enchId, level);
									} else {
										enchTableId = dbEnchTableId;
									}
								}
								plugin.dataQueries.createEnchantLink(enchTableId, 0, itemTableId);
							}
						}
						event.getPlayer().sendMessage(plugin.logPrefix + "Item stack stored.");
					}else{
						event.getPlayer().sendMessage(plugin.logPrefix + "Please hold a stack of item in your hand and right click to deposit them.");						
					}
				}
				event.getPlayer().setItemInHand(null);

			} else {
				if (plugin.permission.has(event.getPlayer(), "wa.use.withdraw.items")) {
					try {
						List<AuctionMail> auctionMail = plugin.dataQueries.getMail(player);
						boolean invFull = true;
						boolean gotMail = false;
						for (AuctionMail mail : auctionMail) {
							boolean enchanted = false;
							List<Integer> enchantments = new ArrayList<Integer>();
							List<Integer> enchLevels = new ArrayList<Integer>();

							List<Integer> enchIDs = plugin.dataQueries.getEnchantIDsForLinks(mail.getId(), 2);
							if (event.getPlayer().getInventory().firstEmpty() != -1) {
								ItemStack stack = mail.getItemStack();
								for (int enchantID : enchIDs) {
									Map<Integer, Integer> enchMap = plugin.dataQueries.getEnchantIDLevel(enchantID);
									for (Map.Entry<Integer, Integer> entry : enchMap.entrySet()) {
										Enchantment tempEnch = Enchantment.getById(entry.getKey());
										// player.sendMessage(tempEnch.getName()+" "+resultEnch.getInt("level"));
										if (tempEnch.canEnchantItem(stack)) {
											// player.sendMessage("Enchanting");
											// stack.addEnchantment(tempEnch,
											// resultEnch.getInt("level"));
											enchantments.add(entry.getKey());
											enchLevels.add(entry.getValue());
											enchanted = true;
										} else {

											// player.sendMessage("Can't enchant for some reason");
										}
										// player.sendMessage(""+stack.containsEnchantment(tempEnch));
									}
								}
								plugin.dataQueries.deleteMail(mail.getId());

								int firstEmpty = -1;
								if (enchanted == true) {
									firstEmpty = event.getPlayer().getInventory().firstEmpty();

								}
								event.getPlayer().getInventory().addItem(stack);
								if (enchanted == true) {
									ItemStack tempStack = event.getPlayer().getInventory().getItem(firstEmpty);
									Iterator<Integer> itr = enchantments.iterator();
									Iterator<Integer> itrl = enchLevels.iterator();
									while ((itr.hasNext()) && (itrl.hasNext())) {
										Enchantment tempEnch = Enchantment.getById(itr.next());
										tempStack.addEnchantment(tempEnch, itrl.next());

									}
								}
								event.getPlayer().updateInventory();
								
								gotMail = true;
								invFull = false;
							} else {
								event.getPlayer().sendMessage(plugin.logPrefix + "Inventory full, cannot get mail");
								invFull = true;
							}
							if (invFull == true) {
								break;
							}
						}
						if (gotMail){
							event.getPlayer().sendMessage(plugin.logPrefix + "Mail retrieved");
						}else{
						  if (!invFull) {
							  event.getPlayer().sendMessage(plugin.logPrefix + "No mail");
						  }
						}
						if (auctionMail.isEmpty()){	
							event.getPlayer().sendMessage(plugin.logPrefix + "No mail");
						}
					} catch (Exception e) {
						e.printStackTrace();
					}
				} else {
					event.getPlayer().sendMessage(plugin.logPrefix + "You do not have permission to use the mailbox");
					event.setCancelled(true);
				}
			}
		}

	}
}
