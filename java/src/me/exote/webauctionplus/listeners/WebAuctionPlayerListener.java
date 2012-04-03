package me.exote.webauctionplus.listeners;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Random;

import me.exote.webauctionplus.WebAuctionPlus;
import me.exote.webauctionplus.dao.AuctionItem;
import me.exote.webauctionplus.dao.AuctionMail;
import me.exote.webauctionplus.dao.AuctionPlayer;
import me.exote.webauctionplus.dao.SaleAlert;

import org.bukkit.GameMode;
import org.bukkit.Material;
import org.bukkit.block.Block;
import org.bukkit.block.Sign;
import org.bukkit.enchantments.Enchantment;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.EventPriority;
import org.bukkit.event.Listener;
import org.bukkit.event.block.Action;
import org.bukkit.event.player.PlayerInteractEvent;
import org.bukkit.event.player.PlayerJoinEvent;
import org.bukkit.event.player.PlayerQuitEvent;
import org.bukkit.inventory.ItemStack;

public class WebAuctionPlayerListener implements Listener {

	private final WebAuctionPlus plugin;

	public WebAuctionPlayerListener(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public static double round(double unrounded, int precision, int roundingMode) {
		BigDecimal bd = new BigDecimal(unrounded);
		BigDecimal rounded = bd.setScale(precision, roundingMode);
		return rounded.doubleValue();
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPlayerQuit(PlayerQuitEvent event) {
		plugin.lastSignUse.remove(event.getPlayer().getName());
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPlayerJoin(PlayerJoinEvent event) {
		Player p = event.getPlayer();
		String player = p.getName();

		// Alert player of new sale alerts
		if (plugin.showSalesOnJoin == true) {
			List<SaleAlert> saleAlerts = plugin.dataQueries.getNewSaleAlertsForSeller(player);
			for (SaleAlert saleAlert : saleAlerts) {
				p.sendMessage(plugin.chatPrefix + "You sold " +
					saleAlert.getQuantity() + saleAlert.getItem() + " to " +
					saleAlert.getBuyer() + " for " + saleAlert.getPriceEach() + " each.");
			}
		}

		// Alert player of new mail
		if (plugin.dataQueries.hasMail(player)) {
			p.sendMessage(plugin.chatPrefix + "You have new mail!");
		}

		// Determine permissions
		int canBuy = 0;
		int canSell = 0;
		int isAdmin = 0;
		if (plugin.permission.has(p, "wa.canbuy")) {
			canBuy = 1;
		}
		if (plugin.permission.has(p, "wa.cansell")) {
			canSell = 1;
		}
		if (plugin.permission.has(p, "wa.webadmin")) {
			isAdmin = 1;
		}

		if (null != plugin.dataQueries.getPlayer(player)) {
			plugin.log.info(plugin.logPrefix + "Player found - " + player +
				" with permissions: canbuy = " + canBuy + " cansell = " +
				canSell + " isAdmin = " + isAdmin);
			// Update permissions
			plugin.dataQueries.updatePlayerPermissions(player, canBuy, canSell, isAdmin);
		}
	}

	@SuppressWarnings("deprecation")
	@EventHandler(priority = EventPriority.NORMAL)
	public void onPlayerInteract(PlayerInteractEvent event) {
		if (event.getAction() != Action.RIGHT_CLICK_BLOCK) {
			return;
		}

		Block block = event.getClickedBlock();
		if (block == null || block.getType() != Material.SIGN_POST) {
			if (block.getType() != Material.WALL_SIGN) {
				return;
			}
		}

		// it's a sign
		Sign sign = (Sign) block.getState();
		String[] lines = sign.getLines();
		if (!lines[0].equals("[WebAuction]")) {
			return;
		}

		Player p = event.getPlayer();
		String player = p.getName();
		event.setCancelled(true);

		// Make sure we can use the sign
		if (plugin.lastSignUse.containsKey(player)) {
			long lastSignUse = plugin.lastSignUse.get(player);
			if (lastSignUse + (long)plugin.signDelay > plugin.getCurrentMilli()) {
//				p.sendMessage(plugin.chatPrefix + "Please wait a bit before using that again");
				return;
			}
		}
		plugin.lastSignUse.put(player, plugin.getCurrentMilli());

		// Shout sign
		if (lines[1].equals("Shout")) {
			Random generator = new Random();
			int roll = generator.nextInt(20);
			switch (roll) {
			case 0:
				p.sendMessage(plugin.chatPrefix + "RAAN MIR TAH!");
				break;
			case 1:
				p.sendMessage(plugin.chatPrefix + "LAAS YAH NIR!");
				break;
			case 2:
				p.sendMessage(plugin.chatPrefix + "FEIM ZII GRON!");
				break;
			case 3:
				p.sendMessage(plugin.chatPrefix + "OD AH VIING!");
				break;
			case 4:
				p.sendMessage(plugin.chatPrefix + "HUN KAL ZOOR!");
				break;
			case 5:
				p.sendMessage(plugin.chatPrefix + "LOK VAH KOOR!");
				break;
			case 6:
				p.sendMessage(plugin.chatPrefix + "ZUN HAAL VIK!");
				break;
			case 7:
				p.sendMessage(plugin.chatPrefix + "FAAS RU MAAR!");
				break;
			case 8:
				p.sendMessage(plugin.chatPrefix + "JOOR ZAH FRUL!");
				break;
			case 9:
				p.sendMessage(plugin.chatPrefix + "SU GRAH DUN!");
				break;
			case 10:
				p.sendMessage(plugin.chatPrefix + "YOL TOOR SHOL!");
				break;
			case 11:
				p.sendMessage(plugin.chatPrefix + "FO KRAH DIIN!");
				break;
			case 12:
				p.sendMessage(plugin.chatPrefix + "LIZ SLEN NUS!");
				break;
			case 13:
				p.sendMessage(plugin.chatPrefix + "KAAN DREM OV!");
				break;
			case 14:
				p.sendMessage(plugin.chatPrefix + "KRII LUN AUS!");
				break;
			case 15:
				p.sendMessage(plugin.chatPrefix + "TIID KLO UL!");
				break;
			case 16:
				p.sendMessage(plugin.chatPrefix + "STRUN BAH QO!");
				break;
			case 17:
				p.sendMessage(plugin.chatPrefix + "ZUL MEY GUT!");
				break;
			case 18:
				p.sendMessage(plugin.chatPrefix + "WULK NAH KEST!");
				break;
			default:
				p.sendMessage(plugin.chatPrefix + "FUS RO DAH!");
				break;
			}

		// Deposit sign (money)
		} else if (lines[1].equals("Deposit")) {
			if (!plugin.permission.has(p, "wa.use.deposit.money")) {
				p.sendMessage(plugin.chatPrefix +
					"You do not have enough money in your pocket.");
			} else {
				double amount = 0.0D;
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
						p.sendMessage(plugin.chatPrefix + "Added " + amount +
							" to auction account, new auction balance: " + currentMoney);
						plugin.dataQueries.updatePlayerMoney(player, currentMoney);
						plugin.economy.withdrawPlayer(player, amount);
					} else {
						p.sendMessage(plugin.chatPrefix +
							"No WebAuction account found, try logging off and back on again");
					}
				}
			}

		// Withdraw sign (money)
		} else if (lines[1].equals("Withdraw")) {
			if (!plugin.permission.has(p, "wa.use.withdraw.money")) {
				p.sendMessage(plugin.chatPrefix +
					"You do not have permission to withdraw money");
				event.setCancelled(true);
			} else {
				double amount = 0.0D;
				if (!lines[2].equals("All")) {
					amount = Double.parseDouble(lines[2]);
				} try {
					AuctionPlayer auctionPlayer = plugin.dataQueries.getPlayer(player);
					if (null == auctionPlayer) {
						p.sendMessage(plugin.chatPrefix +
							"No WebAuction account found, try logging off and back on again");
					} else {
						// Match found!
						double currentMoney = auctionPlayer.getMoney();
						if (lines[2].equals("All")) {
							amount = currentMoney;
						}
						if (currentMoney >= amount) {
							currentMoney -= amount;
							currentMoney = round(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
							p.sendMessage(plugin.chatPrefix + "Removed " +
								amount + " from auction account, new auction balance: " + currentMoney);
							plugin.dataQueries.updatePlayerMoney(player, currentMoney);
							plugin.economy.depositPlayer(player, amount);
						} else {
							p.sendMessage(plugin.chatPrefix +
								"You do not have enough money in your WebAuction account.");
						}
					}
				} catch (Exception e) {
					e.printStackTrace();
				}
			}

		// MailBox Deposit (items)
		} else if (lines[1].equals("MailBox") && lines[2].equals("Deposit")) {
			if (!plugin.permission.has(p, "wa.use.deposit.items")) {
				p.sendMessage(plugin.chatPrefix +
					"You do not have permission to use the mailbox");
				event.setCancelled(true);
			} else if (p.getGameMode() != GameMode.SURVIVAL) {
				p.sendMessage(plugin.chatPrefix + "Hey, no cheating!");
				event.setCancelled(true);
			} else {
				ItemStack stack = p.getItemInHand();
				int itemId = stack.getTypeId();
				if (itemId == 0) {
					p.sendMessage(plugin.chatPrefix +
						"Please hold a stack of item in your hand and right click to deposit them.");
					event.setCancelled(true);
				} else {
					int itemDamage = 0;
					if (stack.getDurability() >= 0) {
						itemDamage = stack.getDurability();
					}
					Map<Enchantment, Integer> itemEnchantments = stack.getEnchantments();
					int quantityInt = stack.getAmount();

					List<AuctionItem> auctionItems = plugin.dataQueries.getItems(player, itemId, itemDamage, false);
					Boolean foundMatch = false;

					for (AuctionItem auctionItem : auctionItems) {
						int itemTableIdNumber = auctionItem.getId();
						List<Integer> enchantmentIds = new ArrayList<Integer>();
						List<Integer> enchantmentIdsStoredTemp;
						for (Map.Entry<Enchantment, Integer> entry : itemEnchantments.entrySet()) {
							Enchantment key = (Enchantment)entry.getKey();
							String enchName = key.getName();
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
							//enchantmentIds.add(enchTableId);
							// player.sendMessage(enchantmentIds.size()+" part1");
						}

						Collections.sort(enchantmentIds);
						enchantmentIdsStoredTemp = plugin.dataQueries.getEnchantIDsForLinks(itemId, 0);
						Collections.sort(enchantmentIdsStoredTemp);

						if (enchantmentIds.equals(enchantmentIdsStoredTemp)) {
							int currentQuantity = auctionItem.getQuantity();
							currentQuantity += quantityInt;
							plugin.dataQueries.updateItemQuantity(currentQuantity, itemTableIdNumber);
							foundMatch = Boolean.valueOf(true);
						} else if (enchantmentIds.isEmpty() && enchantmentIdsStoredTemp.isEmpty()) {
							int currentQuantity = auctionItem.getQuantity();
							currentQuantity += quantityInt;
							plugin.dataQueries.updateItemQuantity(currentQuantity, itemTableIdNumber);
							foundMatch = Boolean.valueOf(true);
						}
					}
					if (!foundMatch.booleanValue()) {
						// Create item
						plugin.dataQueries.createItem(itemId, itemDamage, player, quantityInt);
//						List<AuctionItem> newItems = plugin.dataQueries.getItems(player, itemID, itemDamage, true);

//						int itemTableId = -1;
//						if (!newItems.isEmpty()) {
//							itemTableId = ((AuctionItem)newItems.get(0)).getId();
//						}

						int enchTableId;
						for (Map.Entry<Enchantment, Integer> entry : itemEnchantments.entrySet()) {
							Enchantment key = (Enchantment)entry.getKey();
							String enchName = key.getName();
							int enchId = key.getId();
							Integer level = (Integer)entry.getValue();
							// see if already exists
							for (enchTableId = -1; enchTableId == -1;) {
								int dbEnchTableId = plugin.dataQueries.getEnchantTableID(enchId, level.intValue(), enchName);
								if (dbEnchTableId == -1) {
									plugin.dataQueries.createEnchantment(enchName, enchId, level.intValue());
								} else {
									enchTableId = dbEnchTableId;
								}
							}
						}
					}
					p.sendMessage((new StringBuilder(String.valueOf(plugin.logPrefix))).append("Item stack stored.").toString());
					p.setItemInHand(null);
				}
			}

		// MailBox Withdraw (items)
		} else if (lines[1].equals("MailBox") && lines[2].equals("Withdraw")) {
			if (!plugin.permission.has(p, "wa.use.withdraw.items")) {
				p.sendMessage(plugin.chatPrefix +
					"You do not have permission to use the mailbox");
				event.setCancelled(true);
			} else {
				try {
					List<AuctionMail> auctionMail = plugin.dataQueries.getMail(player);
					boolean invFull = true;
					boolean gotMail = false;
					for (AuctionMail mail : auctionMail) {
						boolean enchanted = false;
						List<Integer> enchantments = new ArrayList<Integer>();
						List<Integer> enchLevels = new ArrayList<Integer>();

						List<Integer> enchIDs = plugin.dataQueries.getEnchantIDsForLinks(mail.getId(), 2);
						if (p.getInventory().firstEmpty() != -1) {
							ItemStack stack = mail.getItemStack();
							for (int enchantID : enchIDs) {
								Map<Integer, Integer> enchMap = plugin.dataQueries.getEnchantIDLevel(enchantID);
								for (Map.Entry<Integer, Integer> entry : enchMap.entrySet()) {
									Enchantment tempEnch = Enchantment.getById(((Integer)entry.getKey()).intValue());
									// p.sendMessage(tempEnch.getName()+" "+resultEnch.getInt("level"));
									if (tempEnch.canEnchantItem(stack)) {
										enchantments.add((Integer)entry.getKey());
										enchLevels.add((Integer)entry.getValue());
										enchanted = true;
									} else {
										plugin.log.info(plugin.logPrefix  + "Can't enchant for some reason");
										p.sendMessage(  plugin.chatPrefix + "Can't enchant for some reason");
									}
								}
							}
							plugin.dataQueries.deleteMail(mail.getId());

							int firstEmpty = -1;
							if (enchanted) {
								firstEmpty = p.getInventory().firstEmpty();
							}
							p.getInventory().addItem(new ItemStack[] {
								stack
							});
							if (enchanted) {
								ItemStack tempStack = p.getInventory().getItem(firstEmpty);
								Iterator<Integer> itr = enchantments.iterator();
								Iterator<Integer> itrl = enchLevels.iterator();
								while ( (itr.hasNext()) && (itrl.hasNext()) ) {
									Enchantment tempEnch = Enchantment.getById(itr.next());
									tempStack.addEnchantment(tempEnch, ((Integer)itrl.next()).intValue());
								}
							}
							p.updateInventory();
							gotMail = true;
							invFull = false;
						} else {
							p.sendMessage(plugin.chatPrefix + "Inventory full, cannot get mail");
							invFull = true;
						}
						if (invFull) {
							break;
						}
					}

					if (gotMail) {
						p.sendMessage(plugin.chatPrefix + "Mail retrieved");
					} else if (!invFull) {
						p.sendMessage(plugin.chatPrefix + "No mail");
					}
					if (auctionMail.isEmpty()) {
						p.sendMessage(plugin.chatPrefix + "No mail");
					}
				} catch(Exception e) {
					e.printStackTrace();
				}
			}
		}
	}

}