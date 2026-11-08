<template>
  <div class="wow2008">
    <div class="wow2008-topbar">
      <div class="wow2008-topbar__inner">
        <nav aria-label="External links">
          <a href="#">WorldofWarcraft.com</a>
          <span class="wow2008-topbar__divider" aria-hidden="true"></span>
          <a href="#">The Armory</a>
          <span class="wow2008-topbar__divider" aria-hidden="true"></span>
          <a href="#">Forums</a>
        </nav>

        <form class="wow2008-search" role="search" @submit.prevent>
          <label class="visually-hidden" for="wow-search">Search</label>
          <span class="wow2008-search__left" aria-hidden="true"></span>
          <span class="wow2008-search__body">
            <input id="wow-search" type="search" placeholder="Search" aria-label="Search site" />
            <select aria-label="Search area">
              <option>Community Site</option>
              <option>Encyclopedia</option>
              <option>In-game Support</option>
              <option>Tech Support</option>
            </select>
            <button type="submit">Search</button>
          </span>
          <span class="wow2008-search__right" aria-hidden="true"></span>
        </form>
      </div>
    </div>

    <div id="bg-top" class="wow2008-bg-top" aria-hidden="true"></div>

    <div id="hp" class="wow2008-stage">
      <header class="wow2008-header">
        <a class="wow2008-logo" href="/" aria-label="World of Warcraft home">
          <img src="/ascemu_tbc/images/templates/The_Burning_Crusade/wowlogo-front.gif" alt="World of Warcraft The Burning Crusade">
        </a>
      </header>

      <aside class="wow2008-left" aria-label="Main navigation">
        <div class="wow2008-ne-top-left" aria-hidden="true"></div>
        <div class="wow2008-ne-center" aria-hidden="true"></div>
        <div class="wow2008-ne-bottom-left" aria-hidden="true"></div>
        <div class="wow2008-ne-nav-bg" aria-hidden="true"></div>

        <nav class="wow2008-nav" aria-label="World of Warcraft sections">
          <section v-for="group in navigation" :key="group.key" class="wow2008-nav-section">
            <div
              class="wow2008-nav-button"
              :class="openGroups.includes(group.key) ? 'menu-button-on' : 'menu-button-off'"
              role="button"
              tabindex="0"
              :aria-expanded="openGroups.includes(group.key)"
              :aria-label="(openGroups.includes(group.key) ? 'Collapse ' : 'Expand ') + group.title"
              @click="toggleGroup(group.key)"
              @keydown.enter.prevent="toggleGroup(group.key)"
              @keydown.space.prevent="toggleGroup(group.key)"
            >
              <span
                class="wow2008-nav-icon"
                :class="{ 'is-wide': group.icon === 'interactive' || group.icon === 'support' }"
                :style="{ backgroundImage: 'url(' + menuAssets + 'icon-' + group.icon + '-' + (openGroups.includes(group.key) ? 'on' : 'off') + '.gif)' }"
                aria-hidden="true"
              ></span>
              <span
                class="wow2008-nav-title"
                :style="{ backgroundImage: 'url(' + menuAssets + 'header-' + group.header + '-' + (openGroups.includes(group.key) ? 'on' : 'off') + '.gif)' }"
                aria-hidden="true"
              ></span>
              <span
                class="wow2008-nav-collapse"
                :class="openGroups.includes(group.key) ? 'is-open' : 'is-closed'"
                aria-hidden="true"
              ></span>
              <span class="wow2008-nav-right-border" aria-hidden="true"></span>
            </div>

            <div v-if="openGroups.includes(group.key)" class="wow2008-nav-inner">
              <div class="leftMenu-cont-top"></div>
              <div class="leftMenu-cont-mid">
                <div class="m-left">
                  <div class="m-right">
                    <div class="leftMenu-cnt">
                      <ul class="mainNav">
                        <li v-for="link in group.links" :key="link">
                          <div class="wow2008-menu-entry">
                            <a href="#" class="menuFiller">{{ link }}</a>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              <div class="leftMenu-cont-bot"></div>
            </div>
          </section>
        </nav>
      </aside>

      <main class="wow2008-center">
        <div class="wow2008-main-top">
          <div><div></div></div>
        </div>

        <div class="wow2008-main-content-wrapper">
          <div class="wow2008-main-content">
            <div class="wow2008-content-padding">
              <div class="wow2008-content-columns">
                <section class="wow2008-main-column">
                  <div class="wow2008-cnt-paper">
                    <div class="wow2008-cnt-top">
                      <div><div></div></div>
                    </div>
                    <div class="wow2008-content-left">
                      <div class="wow2008-content-right">
                        <div class="wow2008-cnt-main">
                          <div class="wow2008-news-column">
                            <div class="wow2008-flash-container">
                              <img class="wow2008-flash-placeholder" src="/ascemu_tbc/images/no-flash.jpg" alt="Welcome to World of Warcraft.com">
                            </div>

                            <slot>
                              <article class="wow2008-news-card">
                                <h1>Welcome to World of Warcraft</h1>
                                <p>Connect your Orbita news topics to publish announcements here.</p>
                              </article>
                            </slot>

                            <div class="wow2008-archive">
                              <a class="wow2008-archive-button" href="#" aria-label="View News Archives"></a>
                            </div>

                            <div class="wow2008-promo-grid">
                              <a class="wow2008-promo" href="#" aria-label="Contests">
                                <div class="wow2008-promo-frame">
                                  <span class="wow2008-promo-icon wow2008-promo-icon--contest"></span>
                                  <h3 class="wow2008-promo-title wow2008-promo-title--contest"><span>Contests</span></h3>
                                  <img src="/ascemu_tbc/images/featured/contest.jpg" alt="World of Warcraft screenshot contest">
                                  <span class="wow2008-read-more" aria-hidden="true"></span>
                                </div>
                              </a>
                              <a class="wow2008-promo" href="#" aria-label="Fan Art">
                                <div class="wow2008-promo-frame">
                                  <span class="wow2008-promo-icon wow2008-promo-icon--fanart"></span>
                                  <h3 class="wow2008-promo-title wow2008-promo-title--fanart"><span>Fan Art</span></h3>
                                  <img src="/ascemu_tbc/images/featured/fanart.jpg" alt="World of Warcraft fan art">
                                  <span class="wow2008-read-more" aria-hidden="true"></span>
                                </div>
                              </a>
                              <a class="wow2008-promo" href="#" aria-label="Wallpapers">
                                <div class="wow2008-promo-frame">
                                  <span class="wow2008-promo-icon wow2008-promo-icon--wallpaper"></span>
                                  <h3 class="wow2008-promo-title wow2008-promo-title--wallpaper"><span>Wallpapers</span></h3>
                                  <img src="/ascemu_tbc/images/featured/wallpaper.jpg" alt="World of Warcraft wallpaper">
                                  <span class="wow2008-read-more" aria-hidden="true"></span>
                                </div>
                              </a>
                            </div>

                            <section id="container-community" class="wow2008-community">
                              <div class="phatLootBox-top wow2008-community-top">
                                <a href="/community/" class="wow2008-community-heading"><h2 class="community"><span class="visually-hidden">Community</span></h2></a>
                                <a href="/community/" class="wow2008-community-visual" aria-label="Community"></a>
                              </div>
                              <div class="phatLootBox-wrapper wow2008-community-wrapper">
                                <div class="community-top">
                                  <h3>Community Spotlight<i> - sch on 04/04/08 </i></h3>
                                </div>
                                <div class="community-cnt">
                                  <div class="community-portrait" aria-hidden="true"><img src="/ascemu_tbc/images/templates/The_Burning_Crusade/icon_communityspotlight.gif" alt=""></div>
                                  <p>
                                    This week we've come across some extremely beautiful and interesting artistic editing of in-game screenshots and characters.
                                    Wyndforge.com is home to an array of screenshots which he’s taken and dressed up with various filters, backdrops, and
                                    effects that turn a normal screenshot into something just a bit fancier. Check out Wyndforge's Art Gallery by
                                    <a href="http://wyndforge.com/" target="new_window" rel="noopener">clicking here</a>.
                                  </p>
                                  <div class="community-watch">
                                    Have you made or seen something that you think deserves to be on the World of Warcraft Community Watch? Send us a link at:
                                    <a href="mailto:">wowcommwatch</a>.
                                  </div>
                                  <p class="community-disclaimer"><small class="blood">
                                    * Links are created by other companies/individuals that are neither affiliated with nor supported by Blizzard Entertainment.
                                  </small></p>
                                  <div class="community-buttons">
                                    <ul>
                                      <li><div class="redButton-container">
                                        <span class="community-button-left"></span>
                                        <span class="community-button-label"><a href="/community/warcraftweekly.html">Warcraft Weekly</a><span class="rdbtn_sh">Warcraft Weekly</span></span>
                                        <span class="community-button-right"></span>
                                      </div></li>
                                      <li><div class="redButton-container">
                                        <span class="community-button-left"></span>
                                        <span class="community-button-label"><a href="/community/communitylinks.html">Spotlight Archive</a><span class="rdbtn_sh">Spotlight Archive</span></span>
                                        <span class="community-button-right"></span>
                                      </div></li>
                                    </ul>
                                  </div>
                                </div>
                              </div>
                              <div class="phatLootBox-bottom wow2008-community-bottom">
                                <a class="wow2008-community-read-more" href="/community/" aria-label="Read More"></a>
                              </div>
                            </section>

                            <div class="wow2008-banner-row">
                              <a class="wow2008-banner wow2008-banner--help" href="#" aria-label="Need Help">
                                <span class="wow2008-banner-icon wow2008-banner-icon--help"></span>
                                <span class="wow2008-banner-label wow2008-banner-label--help"></span>
                                <span class="wow2008-banner-read-more" aria-hidden="true"></span>
                              </a>
                              <a class="wow2008-banner wow2008-banner--jobs" href="#" aria-label="Jobs">
                                <span class="wow2008-banner-icon wow2008-banner-icon--jobs"></span>
                                <span class="wow2008-banner-label wow2008-banner-label--jobs"></span>
                                <span class="wow2008-banner-read-more" aria-hidden="true"></span>
                              </a>
                            </div>

                            <div class="wow2008-link-grid">
                              <section class="wow2008-link-group">
                                <h3><span>WoW: Wrath of the Lich King</span></h3>
                                <div class="wow2008-link-group__content">
                                  <img src="/ascemu_tbc/images/templates/The_Burning_Crusade/image-wrath.gif" alt="">
                                  <ul>
                                    <li><a href="#">Bestiary</a></li>
                                    <li><a href="#">Dragonblight</a></li>
                                    <li><a href="#">Screenshots</a></li>
                                    <li><a href="#">Features</a></li>
                                    <li><a href="#">Movies</a></li>
                                  </ul>
                                </div>
                              </section>
                              <section class="wow2008-link-group">
                                <h3><span>Movies and Trailers</span></h3>
                                <div class="wow2008-link-group__content">
                                  <img src="/ascemu_tbc/images/templates/The_Burning_Crusade/misc-icon-movies.gif" alt="">
                                  <ul>
                                    <li><a href="#">Sunwell Plateau</a></li>
                                    <li><a href="#">Verne Troyer</a></li>
                                    <li><a href="#">M.P.T.</a></li>
                                    <li><a href="#">William Shatner</a></li>
                                  </ul>
                                </div>
                              </section>
                              <section class="wow2008-link-group">
                                <h3><span>World of Warcraft Insider</span></h3>
                                <div class="wow2008-link-group__content">
                                  <img src="/ascemu_tbc/images/templates/The_Burning_Crusade/misc-icon-insider.gif" alt="">
                                  <div>
                                    <p>Your number one source of up-to-date, behind the scenes information about World of Warcraft!</p>
                                    <ul>
                                      <li><a href="#">Latest issue</a></li>
                                      <li><a href="#">Subscribe now</a></li>
                                    </ul>
                                  </div>
                                </div>
                              </section>
                              <section class="wow2008-link-group">
                                <h3><span>Online Store</span></h3>
                                <div class="wow2008-link-group__content">
                                  <img src="/ascemu_tbc/images/templates/The_Burning_Crusade/misc-image-bc.gif" alt="">
                                  <ul>
                                    <li><a href="#">World of Warcraft Battle Chest</a></li>
                                    <li><a href="#">World of Warcraft 2008 Daily Calendar</a></li>
                                    <li><a href="#">Burning Crusade Strategy Guide</a></li>
                                    <li><a href="#">TCG: Onyxia Raid Deck</a></li>
                                  </ul>
                                </div>
                              </section>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="wow2008-cnt-bot">
                      <div><div></div></div>
                    </div>
                  </div>
                </section>

                <aside class="wow2008-right">
                  <section class="wow2008-bc-upgrade">
                    <h2><img src="/ascemu_tbc/images/templates/The_Burning_Crusade/header-expansion.jpg" alt="The Burning Crusade"></h2>
                    <div class="wow2008-bc-upgrade__content">
                      <img src="/ascemu_tbc/images/image-retailbox.gif" alt="World of Warcraft The Burning Crusade">
                      <div>
                        <strong>Digital Upgrade</strong>
                        <span>Burning Crusade</span>
                        <a href="#">Download Now</a>
                      </div>
                    </div>
                  </section>

                  <section class="wow2008-quicklinks">
                    <div class="wow2008-marginal-top">
                      <span class="wow2008-marginal-icon wow2008-marginal-icon--quicklinks"></span>
                      <h2 class="wow2008-quicklinks-title"><img src="/ascemu_tbc/images/header-quick_links.jpg" alt="Quick Links"></h2>
                    </div>
                    <div class="wow2008-marginal-body">
                      <ul>
                        <li v-for="link in quickLinks" :key="link"><a href="#">{{ link }}</a></li>
                      </ul>
                    </div>
                    <div class="wow2008-marginal-bottom"></div>
                  </section>

                  <section class="wow2008-screenshot">
                    <div class="wow2008-marginal-top">
                      <span class="wow2008-marginal-icon wow2008-marginal-icon--ssotd"></span>
                      <h2></h2>
                    </div>
                    <div class="wow2008-marginal-body wow2008-screenshot-body">
                      <div class="wow2008-screenshot__image" role="img" aria-label="Screenshot of the day"></div>
                      <select aria-label="Screenshot gallery">
                        <option>Screenshot Galleries</option>
                      </select>
                    </div>
                    <div class="wow2008-marginal-bottom"></div>
                  </section>

                  <section class="wow2008-newcomers">
                    <div class="wow2008-marginal-top">
                      <span class="wow2008-marginal-icon wow2008-marginal-icon--newcomers"></span>
                      <h2>Newcomers Section</h2>
                    </div>
                    <div class="wow2008-newcomers-body">
                      <div class="wow2008-newcomers-entry">
                        <img src="/ascemu_tbc/images/bg-newcomers.jpg" alt="">
                        <strong>Looking to purchase World of Warcraft?</strong>
                        <span>Follow the links below to learn more.</span>
                      </div>
                      <div class="wow2008-newcomers-entry">
                        <img src="/ascemu_tbc/images/bg-discover.jpg" alt="">
                        <strong>Lost in Azeroth?</strong>
                        <span>Find your way with our Beginner's Guide.</span>
                      </div>
                      <a href="#" class="wow2008-newcomers-button">General FAQ</a>
                    </div>
                    <div class="wow2008-marginal-bottom"></div>
                  </section>
                </aside>
              </div>
            </div>
          </div>
        </div>

        <footer class="wow2008-main-bottom">
          <div><div>
            <div class="wow2008-blizz-logo">
              <img src="/ascemu_tbc/images/new/bot-blizzlogo.gif" alt="Blizzard Entertainment">
            </div>
          </div></div>
        </footer>
        <div class="wow2008-statues" aria-hidden="true">
          <img class="wow2008-statue-left" src="/ascemu_tbc/images/templates/The_Burning_Crusade/statue-left.jpg" alt="">
          <img class="wow2008-statue-right" src="/ascemu_tbc/images/templates/The_Burning_Crusade/statue-right.png" alt="">
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const menuAssets = '/ascemu_tbc/images/menu/leftMenu/'

const navigation = [
  {
    key: 'news',
    title: 'News',
    icon: 'news',
    header: 'news',
    links: ['Current News', 'Archived News', 'Awards and Honors', 'Press Releases'],
  },
  {
    key: 'account',
    title: 'Account',
    icon: 'account',
    header: 'account',
    links: ['Create Account', 'Manage Account', 'Retrieve Password', 'Character Move', 'Paid Char Transfer', 'Recruit-A-Friend', 'Scroll of Resurrection', 'Parental Controls', 'Realm Status', 'Public Test Realm'],
  },
  {
    key: 'gameguide',
    title: 'Game Guide',
    icon: 'gameguide',
    header: 'gameguide',
    links: ['Game Guide', 'Introduction', 'Getting Started', 'Character', 'Reputations', 'Items', 'The World', 'Transportation', 'Player Interaction', 'Player vs Player', 'FAQ', 'Story', 'Burning Crusade', 'Wrath of the Lich King', 'Under Development', 'Previous Patches'],
  },
  {
    key: 'workshop',
    title: 'Workshop',
    icon: 'interactive',
    header: 'workshop',
    links: ['Armory', 'Arena', 'Encyclopedia', 'Talent Calculators', 'Events Calendar', 'Armor Sets', 'World Map', 'Battlegrounds'],
  },
  {
    key: 'media',
    title: 'Media',
    icon: 'media',
    header: 'media',
    links: ['Overview', 'Screenshots', 'Movies', 'Music', 'Wallpapers', 'Other Downloads', 'Gadgetzan Times', 'Blizzard Store', 'BlizzCast'],
  },
  {
    key: 'forums',
    title: 'Forums',
    icon: 'forums',
    header: 'forums',
    links: ['General', 'Class Forums', 'Realm Forums', 'Guild Recruitment', 'Guild Relations', 'Support'],
  },
  {
    key: 'community',
    title: 'Community',
    icon: 'community',
    header: 'community',
    links: ['Overview', 'Comics', 'Machinima', 'Community Spotlight', 'Contests', 'Guild Relations', 'Real-Life Events', 'Fan Art', 'Fan Sites', 'Fan Wallpapers'],
  },
  {
    key: 'support',
    title: 'Support',
    icon: 'support',
    header: 'support',
    links: ['Support News', 'Account Admin', 'Billing and Account', 'Technical Support', 'In-Game Support', 'Support Tools', 'Game Policies'],
  },
]

const openGroups = ref(['account'])

const toggleGroup = (key: string) => {
  openGroups.value = openGroups.value.includes(key)
    ? openGroups.value.filter(group => group !== key)
    : [...openGroups.value, key]
}

const quickLinks = [
  'Account Management',
  'Parental Controls',
  'Support Site',
  'Realm Status',
  'Account Creation',
  'Retrieve Password',
  'Account Security',
  'Latest Patch Notes',
  'Free 10-Day WoW Trial',
]
</script>

<style scoped>
:global(body) {
  margin: 0;
  background: #000;
}

:global(*) {
  box-sizing: border-box;
}

.wow2008 {
  --wow-bg-dark: #05080d;
  --wow-bg-mid: #0a1522;
  --wow-gold: #f5cc6d;
  --wow-gold-strong: #f0b92d;
  --wow-red: #7f1d0a;
  --wow-red-dark: #4a0d05;
  --wow-text: #17120a;
  --wow-link: #1e4378;
  --wow-link-hover: #c60000;
  --wow-panel: rgba(15, 14, 12, 0.82);
  --wow-shadow: rgba(0, 0, 0, 0.7);

  position: relative;
  width: 100%;
  min-height: 100vh;
  min-width: 980px;
  overflow-x: hidden;
  color: var(--wow-text);
  font: 12px/1.45 Arial, Helvetica, sans-serif;
  background: #000 url('/ascemu_tbc/images/templates/The_Burning_Crusade/page-bg.jpg') repeat-x center top;
}

.wow2008-bg-top {
  height: 113px;
  width: 100%;
  position: relative;
  z-index: 0;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/page-bg-top.jpg') repeat-x center top;
}

.wow2008 a {
  color: var(--wow-link);
  text-decoration: underline;
}

.wow2008 a:hover,
.wow2008 a:focus-visible {
  color: var(--wow-link-hover);
}

.wow2008-topbar {
  position: relative;
  z-index: 20;
  height: 32px;
  background: #072540 url('/ascemu_tbc/images/templates/The_Burning_Crusade/bg.gif') 50% 0 repeat-x;
  border-bottom: 1px solid #143352;
}

.wow2008-topbar__inner {
  width: 955px;
  max-width: calc(100% - 24px);
  height: 32px;
  margin: 0 auto;
  padding: 0 17px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/topbg2.gif') no-repeat 50% 0;
}

.wow2008-topbar nav {
  display: flex;
  align-items: center;
  gap: 12px;
  height: 100%;
  white-space: nowrap;
}

.wow2008-topbar nav a {
  color: #fbb917;
  font: bold 11px Tahoma, Arial, sans-serif;
  text-decoration: none;
  text-shadow: 1px 1px #001027;
}

.wow2008-topbar nav a:hover,
.wow2008-topbar nav a:focus-visible {
  color: #fff;
}

.wow2008-topbar__divider {
  display: block;
  width: 2px;
  height: 18px;
  background: linear-gradient(90deg, #172d46, #91a7b8 45%, #172d46);
}

.wow2008-search {
  display: flex;
  align-items: center;
  height: 34px;
  margin: 0;
}

.wow2008-search__left,
.wow2008-search__right {
  display: block;
  width: 16px;
  height: 34px;
}

.wow2008-search__left {
  background: url('/ascemu_tbc/images/layout/search/search-left.gif') no-repeat;
}

.wow2008-search__right {
  background: url('/ascemu_tbc/images/layout/search/search-right.gif') no-repeat;
}

.wow2008-search__body {
  display: flex;
  align-items: center;
  width: 396px;
  height: 34px;
  background: url('/ascemu_tbc/images/layout/search/search-bg.gif') repeat-x;
}

.wow2008-search input,
.wow2008-search select {
  height: 20px;
  border: 1px solid #7f7f7f;
  background: #040d1a;
  color: #ffac04;
  box-sizing: border-box;
}

.wow2008-search input {
  width: 230px;
  margin: 0 2px;
  padding: 2px 6px;
}

.wow2008-search select {
  width: 112px;
  margin: 0 2px;
  font-size: 10px;
}

.wow2008-search button {
  width: 55px;
  height: 21px;
  margin-left: 3px;
  border: 1px solid #8d5e17;
  background: linear-gradient(180deg, #a02d0a, #7d1f09);
  color: #ffdb59;
  font: bold 10px Arial, sans-serif;
  cursor: pointer;
  border-radius: 2px;
}

.wow2008-search button:hover,
.wow2008-search button:focus-visible {
  filter: brightness(1.08);
}

.wow2008-stage {
  position: relative;
  z-index: 1;
  width: 920px;
  margin: -85px auto 0;
  padding-top: 60px;
  display: grid;
  grid-template-columns: 157px 763px;
  align-items: stretch;
}

.wow2008-header {
  position: absolute;
  top: 0;
  left: -16px;
  z-index: 40;
  width: 252px;
  height: 103px;
}

.wow2008-logo,
.wow2008-logo img {
  display: block;
  width: 252px;
  height: 103px;
}

.wow2008-left {
  position: relative;
  grid-column: 1;
  grid-row: 1;
  width: 157px;
  padding-top: 18px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/menu-bg.jpg') repeat-y 65px -13px;
}

.wow2008-ne-top-left {
  position: absolute;
  top: -73px;
  left: -314px;
  width: 264px;
  height: 346px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/ne-top-left.jpg') no-repeat;
  pointer-events: none;
}

.wow2008-ne-center {
  position: absolute;
  top: -73px;
  left: -50px;
  width: 64px;
  height: 578px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/ne-center.jpg') no-repeat;
  pointer-events: none;
}

.wow2008-ne-bottom-left {
  position: absolute;
  top: 267px;
  left: -176px;
  width: 144px;
  height: 126px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/ne-bottom-left.jpg') no-repeat;
  pointer-events: none;
}

.wow2008-ne-nav-bg {
  position: absolute;
  top: -23px;
  left: 11px;
  width: 157px;
  height: 546px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/ne-nav-bg.jpg') no-repeat;
  pointer-events: none;
}

.wow2008-nav {
  position: relative;
  z-index: 4;
  width: 157px;
  margin: 36px 0 0;
  padding: 0;
  right: -16px;
}

.wow2008-nav-section {
  position: relative;
  min-height: 31px;
}

.wow2008-nav-button {
  position: relative;
  z-index: 2;
  width: 142px;
  height: 27px;
  margin-top: 1px;
  cursor: pointer;
  background-repeat: no-repeat;
  background-position: 1px 0;
}

.wow2008-nav-button.menu-button-on {
  background-image: url('/ascemu_tbc/images/menu/leftMenu/leftMenu-button-on.gif');
}

.wow2008-nav-button.menu-button-off {
  background-image: url('/ascemu_tbc/images/menu/leftMenu/leftMenu-button-off.gif');
}

.wow2008-nav-icon {
  position: absolute;
  left: 5px;
  top: 3px;
  display: block;
  width: 24px;
  height: 21px;
  background-repeat: no-repeat;
}

.wow2008-nav-icon.is-wide {
  width: 25px;
}

.wow2008-nav-title {
  position: absolute;
  left: 30px;
  top: 1px;
  display: block;
  width: 105px;
  height: 25px;
  background-repeat: no-repeat;
  background-position: 0 5px;
}

.wow2008-nav-collapse {
  position: absolute;
  top: 9px;
  right: -2px;
  display: block;
  width: 15px;
  height: 14px;
  background-repeat: no-repeat;
  background-position: 0 0;
}

.wow2008-nav-collapse.is-open {
  background-image: url('/ascemu_tbc/images/leftmenu-minus.gif');
}

.wow2008-nav-collapse.is-closed {
  background-image: url('/ascemu_tbc/images/leftmenu-plus.gif');
}

.wow2008-nav-right-border {
  position: absolute;
  right: -10px;
  top: -4px;
  display: block;
  width: 9px;
  height: 35px;
  background: url('/ascemu_tbc/images/menu-add.gif') no-repeat 0 0;
}

.wow2008-nav-inner {
  position: relative;
  z-index: 2;
  width: 145px;
  padding: 0 0 0 4px;
}

.leftMenu-cont-top {
  width: 145px;
  height: 6px;
  background: url('/ascemu_tbc/images/leftMenu-cont-top.png') no-repeat 0 0;
}

.leftMenu-cont-mid {
  width: 145px;
  background: url('/ascemu_tbc/images/menu/mainmenu/leftMenu-back.png') repeat 0 0;
}

.leftMenu-cont-mid .m-left {
  background: url('/ascemu_tbc/images/leftMenu-cont-mid-left.jpg') repeat-y top left;
}

.leftMenu-cont-mid .m-left .m-right {
  background: url('/ascemu_tbc/images/leftMenu-cont-mid-right.jpg') repeat-y top right;
}

.leftMenu-cnt {
  padding-left: 5px;
}

.leftMenu-cont-bot {
  width: 145px;
  height: 8px;
  background: url('/ascemu_tbc/images/leftMenu-cont-bot.png') no-repeat 0 0;
}

.mainNav {
  list-style: none;
  margin: 0;
  padding: 0;
}

.mainNav li {
  margin: 0;
  padding: 0;
}

.wow2008-menu-entry {
  position: relative;
  left: -3px;
  width: 139px;
  height: 15px;
  margin: 0;
  padding: 0 0 0 10px;
  background: url('/ascemu_tbc/images/menu/mainmenu/bullet-trans-bg.gif') no-repeat 0 0;
  font: bold 11px/15px Arial, Helvetica, sans-serif;
}

.wow2008-menu-entry a,
.wow2008-menu-entry a:link,
.wow2008-menu-entry a:visited {
  display: block;
  width: 120px;
  color: #d0a803;
  font: bold 11px/15px Arial, Helvetica, sans-serif;
  text-decoration: none;
}

.wow2008-menu-entry a:hover,
.wow2008-menu-entry a:focus-visible {
  color: #fff;
}

.wow2008-center {
  position: relative;
  grid-column: 2;
  grid-row: 1;
  width: 763px;
  background: url('/ascemu_tbc/images/parchment/plain/light2.jpg') repeat 0 0;
}

.wow2008-main-top {
  width: 100%;
  height: 25px;
  position: relative;
  z-index: 2;
  background: url('/ascemu_tbc/images/layout/frame_top-bg.jpg') repeat-x 0 0;
}

.wow2008-main-top > div {
  height: 25px;
  background: url('/ascemu_tbc/images/layout/frame_top-left.jpg') no-repeat top left;
}

.wow2008-main-top > div > div {
  height: 25px;
  background: url('/ascemu_tbc/images/layout/frame_top-right.jpg') no-repeat top right;
}

.wow2008-main-content-wrapper {
  background: url('/ascemu_tbc/images/layout/frame_left-bg.jpg') repeat-y 0 0;
}

.wow2008-main-content {
  background:
    url('/ascemu_tbc/images/layout/frame_right-bg.jpg') repeat-y top right,
    url('/ascemu_tbc/images/parchment/plain/light2.jpg') repeat 0 0;
}

.wow2008-content-padding {
  position: relative;
  margin: 0 19px 0 17px;
  padding: 10px 10px 0;
  background: url('/ascemu_tbc/images/parchment/plain/light2.jpg') repeat 0 0;
}

.wow2008-content-columns {
  display: grid;
  grid-template-columns: 470px 220px;
  column-gap: 8px;
  width: 698px;
  margin: 3px 0 0 3px;
}

.wow2008-main-column {
  width: 470px;
  min-width: 0;
}

.wow2008-cnt-paper {
  width: 470px;
  background: url('/ascemu_tbc/images/layout/light.jpg') repeat 0 0;
}

.wow2008-cnt-top {
  height: 13px;
  background: url('/ascemu_tbc/images/layout/cnt-paper-top.jpg') repeat-x top left;
}

.wow2008-cnt-top > div {
  height: 13px;
  background: url('/ascemu_tbc/images/layout/cnt-paper-topleft.jpg') no-repeat top left;
}

.wow2008-cnt-top > div > div {
  height: 13px;
  background: url('/ascemu_tbc/images/layout/cnt-paper-topright.jpg') no-repeat top right;
}

.wow2008-content-left {
  background: url('/ascemu_tbc/images/layout/cnt-paper-left.jpg') repeat-y top left;
}

.wow2008-content-right {
  background: url('/ascemu_tbc/images/layout/cnt-paper-right.jpg') repeat-y top right;
}

.wow2008-cnt-main {
  padding: 0 0 10px;
  background: url('/ascemu_tbc/images/layout/light.jpg') repeat 0 0;
}

.wow2008-cnt-bot {
  height: 7px;
  background: url('/ascemu_tbc/images/layout/cnt-paper-bot.jpg') repeat-x bottom left;
}

.wow2008-cnt-bot > div {
  height: 7px;
  background: url('/ascemu_tbc/images/layout/cnt-paper-botleft.jpg') no-repeat 0 100%;
}

.wow2008-cnt-bot > div > div {
  height: 7px;
  background: url('/ascemu_tbc/images/layout/cnt-paper-botright.jpg') no-repeat bottom right;
}

.wow2008-news-column {
  width: 470px;
  min-width: 0;
}

.wow2008-flash-container {
  width: 470px;
  height: 190px;
  margin: 0 0 12px;
}

.wow2008-flash-placeholder {
  display: block;
  width: 474px;
  height: auto;
  margin: 0 0 0 -2px;
}

.wow2008-news-card {
  padding: 16px 18px 18px;
  background: #f1dfb4;
  border: 1px solid rgba(130, 98, 34, 0.75);
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.3);
}

.wow2008-news-card h1 {
  margin: 0 0 8px;
  font: 22px/1.2 Georgia, 'Times New Roman', serif;
  color: #17416a;
}

.wow2008-news-card p {
  margin: 0;
  font-size: 12px;
  line-height: 1.5;
  color: #221b14;
}

.wow2008-archive {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 470px;
  height: 31px;
  background: url('/ascemu_tbc/images/archives.jpg') no-repeat center top;
}

.wow2008-archive-button {
  display: block;
  width: 136px;
  height: 15px;
  background: url('/ascemu_tbc/images/news-archives.gif') no-repeat 0 0;
  line-height: 0;
  transform: translateY(-5px);
}

.wow2008-archive-button:hover,
.wow2008-archive-button:focus-visible,
.wow2008-archive-button:active {
  background-position: 0 -15px;
}

.wow2008-promo-grid {
  display: grid;
  grid-template-columns: repeat(3, 150px);
  gap: 10px;
  margin: 12px 0 10px;
}

.wow2008-promo {
  position: relative;
  display: block;
  width: 150px;
  height: 123px;
  text-decoration: none;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/community-smallteaser-bg.gif') no-repeat 0 0;
}

.wow2008-promo:nth-child(n + 2) {
  transform: translateX(1px);
}

.wow2008-promo-frame {
  position: relative;
  width: 150px;
  height: 123px;
}

.wow2008-promo-frame > img {
  display: block;
  width: 133px;
  height: 93px;
  margin: 20px 0 0 7px;
  border: 0;
}

.wow2008-promo-icon {
  position: absolute;
  z-index: 2;
  top: -25px;
  left: -2px;
  display: block;
  width: 36px;
  height: 36px;
  background-repeat: no-repeat;
}

.wow2008-promo-icon--contest {
  background-image: url('/ascemu_tbc/images/visual-smallTeaser-contests.gif');
}

.wow2008-promo-icon--fanart {
  background-image: url('/ascemu_tbc/images/visual-smallTeaser-fanarts.gif');
}

.wow2008-promo-icon--wallpaper {
  top: -32px;
  height: 43px;
  background-image: url('/ascemu_tbc/images/visual-smallTeaser-wallp.gif');
}

.wow2008-promo-title {
  position: absolute;
  z-index: 2;
  top: -20px;
  left: 40px;
  width: 105px;
  height: 20px;
  margin: 0;
  background-repeat: no-repeat;
  background-position: 0 3px;
}

.wow2008-promo-title span {
  display: none;
}

.wow2008-promo-title--contest {
  background-image: url('/ascemu_tbc/images/header-contest.gif');
}

.wow2008-promo-title--fanart {
  background-image: url('/ascemu_tbc/images/header-fanarts.gif');
}

.wow2008-promo-title--wallpaper {
  background-image: url('/ascemu_tbc/images/header-wallpapers.gif');
}

.wow2008-read-more {
  position: absolute;
  z-index: 3;
  right: 6px;
  bottom: 28px;
  display: block;
  width: 75px;
  height: 15px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/button-readmore.gif') no-repeat;
}

.wow2008-community {
  position: relative;
  width: 470px;
  margin: 10px 0 12px;
  color: #17120a;
  font: 11px/1.35 "Trebuchet MS", Arial, sans-serif;
}

.wow2008-community-top {
  position: relative;
  height: 25px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/phatlootbox-top.gif') no-repeat 0 0;
}

.wow2008-community-heading {
  position: absolute;
  top: 5px;
  left: 38px;
  display: block;
  width: 120px;
  height: 27px;
  background: url('/ascemu_tbc/images/header-comm.gif') no-repeat 0 0;
}

.wow2008-community-heading h2 {
  width: 100%;
  height: 100%;
  margin: 0;
}

.wow2008-community-visual {
  position: absolute;
  z-index: 2;
  top: -9px;
  left: 0;
  display: block;
  width: 46px;
  height: 54px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/visual-community.gif') no-repeat 0 0;
}

.wow2008-community-wrapper {
  min-height: 210px;
  padding: 0 12px 10px;
  background: url('/ascemu_tbc/images/community-bg.jpg') no-repeat 6px 100%, url('/ascemu_tbc/images/phatLootBox-bg.gif') repeat-y 0 0;
  box-sizing: border-box;
}

.wow2008-community .community-top {
  position: relative;
  min-height: 25px;
  padding: 3px 8px 0 10px;
}

.wow2008-community .community-top::before {
  content: "";
  position: absolute;
  z-index: 0;
  inset: -3px -7px 0 -8px;
  background: url('/ascemu_tbc/images/phatLootBox-hdlbg.jpg') repeat-x 0 0;
}

.wow2008-community .community-top h3 {
  position: relative;
  z-index: 1;
  margin: 0;
  color: #fff;
  font: bold 12px/20px Georgia, 'Times New Roman', serif;
}

.wow2008-community .community-top h3 i {
  color: #fff4cf;
  font: normal 10px Arial, sans-serif;
}

.wow2008-community .community-cnt {
  position: relative;
  min-height: 175px;
  padding: 9px 10px 8px;
}

.wow2008-community .community-cnt > p {
  margin: 0 0 8px;
  padding: 0 91px 0 0;
  font: 11px/1.4 "Trebuchet MS", Arial, sans-serif;
}

.wow2008-community .community-cnt a {
  color: #17416a;
  text-decoration: underline;
}

.wow2008-community .community-cnt a:hover {
  color: #9b1c12;
}

.wow2008-community .community-portrait {
  position: relative;
  float: right;
  width: 82px;
  height: 83px;
  margin: 0 0 6px 9px;
}

.wow2008-community .community-portrait img {
  position: absolute;
  top: 8px;
  left: 8px;
  display: block;
  width: 64px;
  height: 64px;
  object-fit: cover;
}

.wow2008-community .community-portrait::after {
  content: "";
  position: absolute;
  inset: 0;
  width: 82px;
  height: 83px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/portrait-frame.gif') no-repeat 0 0;
}

.wow2008-community .community-watch {
  clear: both;
  margin: 8px 0 7px;
  padding: 7px 9px;
  color: #241b10;
  background: #e4d1a8;
  border: 1px solid #c5ad7c;
  font: 10px/1.35 "Trebuchet MS", Arial, sans-serif;
}

.wow2008-community .community-disclaimer {
  margin: 5px 0 8px;
  padding: 0;
  font-size: 9px;
}

.wow2008-community .blood {
  color: #6c1b12;
}

.wow2008-community .community-buttons ul {
  display: flex;
  gap: 8px;
  align-items: center;
  list-style: none;
  margin: 8px 0 0;
  padding: 0 0 0 5px;
}

.wow2008-community .community-buttons li {
  margin: 0;
  padding: 0;
}

.wow2008-community .redButton-container {
  display: flex;
  align-items: stretch;
  height: 22px;
  white-space: nowrap;
}

.wow2008-community .community-button-left,
.wow2008-community .community-button-right {
  display: block;
  flex: 0 0 auto;
  height: 22px;
  background-repeat: no-repeat;
}

.wow2008-community .community-button-left {
  width: 5px;
  background-image: url('/ascemu_tbc/images/templates/The_Burning_Crusade/button-left.gif');
}

.wow2008-community .community-button-right {
  width: 7px;
  background-image: url('/ascemu_tbc/images/templates/The_Burning_Crusade/button-right.gif');
}

.wow2008-community .community-button-label {
  position: relative;
  width: 110px;
  height: 22px;
  display: block;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/button-center.gif') repeat-x 0 0;
  text-align: center;
  font: bold 11px/22px Arial, sans-serif;
}

.wow2008-community .community-button-label a,
.wow2008-community .community-button-label .rdbtn_sh {
  position: absolute;
  inset: 0;
  display: block;
  width: 110px;
  height: 22px;
  color: #fff4cf;
  text-decoration: none;
}

.wow2008-community .community-button-label .rdbtn_sh {
  display: none;
}

.wow2008-community .community-button-label a {
  z-index: 1;
  text-shadow: 1px 1px 1px #3b0905, -1px 0 #3b0905, 0 1px #3b0905;
}

.wow2008-community-bottom {
  position: relative;
  height: 28px;
  background: url('/ascemu_tbc/images/phatLootBox-bottom.jpg') no-repeat 0 -1px;
}

.wow2008-community-read-more {
  position: absolute;
  right: 6px;
  bottom: 16px;
  display: block;
  width: 75px;
  height: 15px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/button-readmore.gif') no-repeat 0 0;
}

.wow2008-banner-row {
  display: grid;
  grid-template-columns: 226px 226px;
  gap: 8px;
  margin: 12px 0 18px;
}

.wow2008-banner {
  position: relative;
  display: block;
  width: 226px;
  height: 91px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/service-smallteaser-bg.gif') no-repeat 0 0;
}

.wow2008-banner--help {
  background-image: url('/ascemu_tbc/images/templates/The_Burning_Crusade/service-smallteaser-bg.gif'), url('/ascemu_tbc/images/needhelp-bg.jpg');
  background-repeat: no-repeat, no-repeat;
  background-position: 0 0, 8px 22px;
}

.wow2008-banner--jobs {
  transform: translateX(12px);
  background-image: url('/ascemu_tbc/images/templates/The_Burning_Crusade/service-smallteaser-bg.gif'), url('/ascemu_tbc/images/jobs-bg.jpg');
  background-repeat: no-repeat, no-repeat;
  background-position: 0 0, 8px 22px;
}

.wow2008-banner-read-more {
  position: absolute;
  z-index: 3;
  right: 8px;
  bottom: 8px;
  display: block;
  width: 75px;
  height: 15px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/button-readmore.gif') no-repeat 0 0;
}

.wow2008-banner-icon {
  position: absolute;
  z-index: 2;
  top: -4px;
  left: 0;
  display: block;
  width: 36px;
  height: 35px;
  background-repeat: no-repeat;
}

.wow2008-banner-icon--help {
  background-image: url('/ascemu_tbc/images/visual-smallTeaser-needhelp.gif');
}

.wow2008-banner-icon--jobs {
  background-image: url('/ascemu_tbc/images/visual-smallTeaser-jobs.gif');
}

.wow2008-banner-label {
  position: absolute;
  top: 3px;
  left: 38px;
  width: 105px;
  height: 20px;
  background-repeat: no-repeat;
}

.wow2008-banner-label--help {
  background-image: url('/ascemu_tbc/images/header-needhelp.gif');
}

.wow2008-banner-label--jobs {
  background-image: url('/ascemu_tbc/images/header-jobs.gif');
}

.wow2008-link-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px 16px;
  padding: 0 5px 20px;
}

.wow2008-link-group h3 {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  height: 29px;
  margin: -13px 0 7px;
  padding: 0 8px;
  color: #173d63;
  font: bold 10px/1 Georgia, 'Times New Roman', serif;
  background: none;
}

.wow2008-link-group h3::before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 1px;
  left: 0;
  width: 100%;
  height: 29px;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/miscbox-top.gif') repeat-x 0 0;
  pointer-events: none;
}

.wow2008-link-group h3 span {
  position: relative;
  z-index: 1;
  transform: translate(12px, 10px);
  color: #fff;
}

.wow2008-link-group__content {
  display: flex;
  align-items: flex-start;
  gap: 6px;
}

.wow2008-link-group__content > img {
  display: block;
  flex: 0 0 54px;
  width: 54px;
  height: 50px;
  object-fit: contain;
}

.wow2008-link-group__content > ul,
.wow2008-link-group__content > div {
  flex: 1 1 auto;
}

.wow2008-link-group__content p {
  margin: 0 0 4px;
  font: 12px/1.25 Arial, Helvetica, sans-serif;
}

.wow2008-link-group ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.wow2008-link-group li {
  min-height: 14px;
  padding-left: 16px;
  line-height: 14px;
  background: url('/ascemu_tbc/images/icons/bullet.gif') no-repeat 0 0;
}

.wow2008-link-group li a {
  font: bold 9px/1.35 Arial, Helvetica, sans-serif;
  color: #1e4378;
  text-decoration: underline;
}

.wow2008-right {
  position: relative;
  width: 220px;
  min-width: 0;
  padding: 0;
  background: url('/ascemu_tbc/images/parchment/plain/light2.jpg') repeat 0 0;
}

.wow2008-bc-upgrade {
  width: 213px;
  margin: 0 auto 12px;
  position: relative;
}

.wow2008-bc-upgrade h2 {
  height: 27px;
  margin: 0;
  background: url('/ascemu_tbc/images/templates/The_Burning_Crusade/marginal-infobox-top.jpg') no-repeat 43px 0;
}

.wow2008-bc-upgrade h2 img {
  display: block;
  width: 121px;
  margin-left: 48px;
}

.wow2008-bc-upgrade__content {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  min-height: 95px;
  padding: 6px 9px;
  background: url('/ascemu_tbc/images/marginal-infoBox-bg.gif') repeat-y 0 0;
  font: 11px/1.25 Arial, sans-serif;
}

.wow2008-bc-upgrade__content > img {
  width: 55px;
  height: 91px;
  object-fit: contain;
}

.wow2008-bc-upgrade__content strong,
.wow2008-bc-upgrade__content span,
.wow2008-bc-upgrade__content a {
  display: block;
  margin-bottom: 4px;
}

.wow2008-bc-upgrade__content a {
  padding: 2px 5px;
  background: #8c260d;
  color: #f9df85;
  font-size: 10px;
  font-weight: bold;
  text-align: center;
  text-decoration: none;
}

.wow2008-quicklinks,
.wow2008-screenshot,
.wow2008-newcomers {
  width: 213px;
  margin: 0 auto 12px;
  position: relative;
}

.wow2008-marginal-top {
  position: relative;
  width: 213px;
  height: 27px;
  background: url('/ascemu_tbc/images/marginal-infoBox-top-ie.jpg') no-repeat 43px 0;
}

.wow2008-marginal-top h2 {
  position: absolute;
  top: 5px;
  left: 30px;
  width: 150px;
  height: 20px;
  margin: 0;
  background-repeat: no-repeat;
  background-position: 0 0;
}

.wow2008-quicklinks .wow2008-marginal-top h2 {
  left: 50px;
  width: 78px;
  height: 14px;
  background: none;
}

.wow2008-quicklinks-title img {
  display: block;
  width: 78px;
  height: 14px;
}

.wow2008-screenshot .wow2008-marginal-top h2 {
  background-image: url('/ascemu_tbc/images/header-ssotd.gif');
}

.wow2008-newcomers .wow2008-marginal-top h2 {
  background-image: url('/ascemu_tbc/images/header-newcomers.jpg');
}

.wow2008-marginal-icon {
  position: absolute;
  z-index: 3;
  top: -14px;
  left: -3px;
  width: 46px;
  height: 54px;
  background-repeat: no-repeat;
}

.wow2008-marginal-icon--quicklinks {
  background-image: url('/ascemu_tbc/images/visual-infoBox-bg.gif');
}

.wow2008-marginal-icon--ssotd {
  top: -6px;
  background-image: url('/ascemu_tbc/images/visual-infoBox-ssotd.gif');
}

.wow2008-marginal-icon--newcomers {
  top: -6px;
  background-image: url('/ascemu_tbc/images/visual-infoBox-ng.gif');
}

.wow2008-marginal-body {
  box-sizing: border-box;
  width: 213px;
  padding: 0 8px 6px;
  background: url('/ascemu_tbc/images/marginal-infoBox-bg.gif') repeat-y 0 0;
}

.wow2008-quicklinks ul {
  list-style: none;
  margin: 0;
  padding: 0 10px 5px;
}

.wow2008-quicklinks li {
  margin: 0 10px 0 0;
  padding: 3px 0;
  border-bottom: 1px solid #af9771;
}

.wow2008-quicklinks a {
  display: block;
  padding-left: 20px;
  color: #1e4378;
  font: bold 11px Arial, sans-serif;
  text-decoration: none;
  background: url('/ascemu_tbc/images/icons/bullet.gif') no-repeat 0 40%;
}

.wow2008-quicklinks a:hover {
  color: #c60000;
}

.wow2008-marginal-bottom {
  height: 10px;
  background: url('/ascemu_tbc/images/marginal-infoBox-bottom.gif') no-repeat 0 0;
}

.wow2008-screenshot-body {
  padding: 0 8px 6px;
  background: #050607 url('/ascemu_tbc/images/marginal-infoBox-bg.gif') repeat-y 0 0;
}

.wow2008-screenshot__image {
  width: 197px;
  height: 125px;
  background: #030405 url('/ascemu_tbc/images/visual-infoBox-ssotd.gif') no-repeat 0 0;
}

.wow2008-screenshot select {
  display: block;
  width: 179px;
  height: 17px;
  margin: 3px auto 0;
  border: 1px solid #835a20;
  background: #ae813e;
  color: #17120a;
  font-size: 10px;
}

.wow2008-newcomers-body {
  box-sizing: border-box;
  width: 213px;
  padding: 7px 10px 8px;
  background: url('/ascemu_tbc/images/marginal-infoBox-bg.gif') repeat-y 0 0;
  font: 10px/1.25 Arial, sans-serif;
}

.wow2008-newcomers-entry {
  position: relative;
  min-height: 67px;
  margin: 0 0 6px;
  padding-left: 65px;
}

.wow2008-newcomers-entry img {
  position: absolute;
  top: 0;
  left: 0;
  width: 58px;
  height: 58px;
  object-fit: cover;
}

.wow2008-newcomers-entry strong,
.wow2008-newcomers-entry span {
  display: block;
}

.wow2008-newcomers-button {
  display: block;
  width: 103px;
  margin: 4px auto 0;
  padding: 3px 6px;
  text-align: center;
  color: #ffdc50 !important;
  background: #7d1c08;
  border: 2px ridge #9b793e;
  text-decoration: none !important;
  font-weight: bold;
}

.wow2008-main-bottom {
  position: relative;
  z-index: 10;
  width: 763px;
  height: 41px;
  background: url('/ascemu_tbc/images/layout/frame_bottom-bg.jpg') repeat-x 0 0;
}

.wow2008-main-bottom > div {
  height: 41px;
  background: url('/ascemu_tbc/images/layout/frame_bottom-left.jpg') no-repeat top left;
}

.wow2008-main-bottom > div > div {
  height: 41px;
  background: url('/ascemu_tbc/images/layout/frame_bottom-right.jpg') no-repeat top right;
}

.wow2008-blizz-logo {
  position: relative;
  display: block;
  top: -16px;
  z-index: 500;
  text-align: center;
}

.wow2008-blizz-logo img {
  display: inline-block;
  width: 97px;
  height: 57px;
}

.wow2008-statues {
  position: relative;
  z-index: 2;
  width: 100%;
  height: 0;
  pointer-events: none;
}

.wow2008-statue-left {
  position: absolute;
  top: -461px;
  left: -243px;
  width: 243px;
  height: 445px;
  z-index: 2;
}

.wow2008-statue-right {
  position: absolute;
  top: -358px;
  right: -95px;
  width: 95px;
  height: 342px;
  z-index: 2002;
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (max-width: 1100px) {
  .wow2008 {
    min-width: 920px;
  }
}
</style>
