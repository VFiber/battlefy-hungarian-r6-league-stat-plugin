# Battlefy Hungarian R6 League Stat Plugin Plugin

The **Battlefy Hungarian R6 League Stat Plugin** Plugin egy kiterjesztése a [Grav CMS](http://github.com/getgrav/grav)nek.

This plugin is developed specificly for our (Wargasz Esport) purpose, not suitable for generic use.

A Plugin kifejezetten a Wargasz Esport-nak készült, jelen formájában nem alkalmas általános használatra.

## Használat

A Battlefy rendszere alpvetően nem ad API-t, így kerülő megoldásként a "Bracket" megnyitásakor keletkező "matches" API hívás válaszát egy "kupaneve.bfjson" formátumban lementve, a cikk alá feltöltve, majd shortcode-al hivatkozva automatikusan kiszámolja az adott csapat pontjait.

A kiterjesztés gyakorlatilag mindegy, viszont a .json fájlok használatát a GRAV több helyen akadályozza, így - praktikumból - lett a .bfjson kiterjesztés, a tartalma ennek ellenére szabványos JSON.

Azokat a mérkőzéseket, ahol nem ért el valamelyik csapat legalább 7 pontot, a rendszer automatikusan érvénytelen mérkőzésnek vesz, mivel nincs más módunk megkülönböztetni a normál mérkőzéseket azoktól, ahol valamelyik csapat büntetése miatt fejeződött be a mérkőzés. Az oka, hogy nem szeretnénk támogatni a "ruleshark" mentalitást, ahol egy-egy csapat a szabályokat önös céljaira felhasználva nyer mérkőzéseket.

Shortcode szintaktikát használ:

```[battlefy filelist=nc_1.json,nc_2.json,nc_3.json,nc_4.bfjson,nc_5.bfjson bannedTeamList=ban.bfjson roundsToQualify=3]```

Paraméterek:
- ```filelist``` - vesszővel elválasztott fájlnevek listája. A fájl neve nem tartalmazhat szóközt és speciális karaktert.
- ```bannedTeamList``` - fájlnév, tartalma a csapat állandó azonosítója egy JSON tömbként. Ezen csapatokat a rendszer automatikusan kizárja feldolgozás közben.
- ```roundsToQualify``` - egész szám - a kvalifikációhoz szükséges "fájlok" / kupák száma.

A táblázat úgy lett kialakítva, hogy rendezésnél azokat a csapatokat veszi előre, akik kvalifikáltak, majd másodlagos rendezésre a pontszámot teszi, és 8 csapatonként lapozható táblázatot renderel ki.


## Lehetséges bugok
Amennyiben egy csapat nem megfelelő eredményt ad le (0-1), az boríthatja a bónusz pontszámítást, mert nincs külön a JSON fájlban jelölve az, hogy első-második-harmadik hely kinek lett kiosztva, így a legmagasabb megnyert "round" szám lesz a győztes és a "looser" bracket győztese - megfelelő pontszám esetén - kap +1 pontot.

## Működés

A rendszer beolvassa a fájlokat, minden fájlt 1 kupa eredményének értelmezi. A kupa adatait elemzi, minden érvényes mérkőzés pontszámát összeadja, majd a fenti algoritmus alapján eldönti, hogy ki a kupa győztese, 1, 2, és 3. helyezettje.

A feldolgozás végén összeáll egy struktúra ami tartalmazza a csapatok nevét és pontjait, így mindegyik fájlhoz tartozik egy ilyen struktúra.

A fájlokból generált struktúrákat összegzi, majd ebből egy táblázatot generál.

## To Do

- [ ] A plugin későbbiekben bővíthető lehetne a mérkőzésekkel, illetve a kupák eredményeinek böngészésével, statisztikákkal, de ez jelenleg nem prioritás

