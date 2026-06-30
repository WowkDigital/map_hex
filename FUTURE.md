# Plan rozwoju i pomysły na rozbudowę HexTravel Log 🗺️⬡

HexTravel Log to świetny fundament pod rozbudowaną aplikację turystyczną. Heksy Uber H3 dają ogromne możliwości analityczne i wizualne. Poniżej znajduje się lista propozycji ulepszeń podzielona na kategorie, które mogą przekształcić ten projekt w pełnoprawną platformę podróżniczą.

---

## 1. Użyteczność i UX (User Experience)

### 📍 Lokalizacja GPS i Wyszukiwarka Miejsc
* **Przycisk "Moja lokalizacja":** Wykorzystanie systemowego API Geolocation w przeglądarce do natychmiastowego wycentrowania mapy na pozycji użytkownika i opcjonalnie zaznaczenia aktualnego heksa.
* **Geokoder (Wyszukiwarka):** Integracja z darmowym API (np. Nominatim/OpenStreetMap) umożliwiającym wyszukanie miasta lub kraju i szybkie przeniesienie tam widoku.

### 👥 Rozbudowane Profile Użytkowników
* **Zarządzanie profilami:** Dodanie opcji edycji nazwy użytkownika oraz usuwania profilu bezpośrednio z poziomu interfejsu (UI).
* **Indywidualna kolorystyka:** Możliwość przypisania unikalnego koloru dla każdego użytkownika (zamiast sztywnego koloru szmaragdowego), co pozwoli na nakładanie map kilku osób i porównywanie ich (np. "Gdzie byliśmy razem?").

### ✍️ Interaktywne Wspomnienia (Notatki i Zdjęcia)
* **Szczegóły heksa:** Kliknięcie w odwiedzony heks otwiera wyskakujące okienko (popup/modal), w którym użytkownik może:
  * Dodać precyzyjną datę wizyty (lub zakres dat).
  * Wpisać krótką notatkę/dziennik z podróży.
  * Dodać zdjęcia (zapisywane na serwerze lub jako linki).
* **Galeria podróży:** Widok podsumowujący wszystkie dodane zdjęcia z mapy w formie ładnej siatki.

### 📥 Import / Eksport Danych (Ślady GPS)
* **Import plików GPX / GeoJSON:** Najlepsza funkcja dla aktywnych podróżników. Użytkownik wgrywa plik `.gpx` ze swojego zegarka (Garmin, Strava) lub telefonu, a aplikacja automatycznie oblicza, które heksy przecinała trasa i oznacza je jako odwiedzone.
* **Kopia zapasowa (Backup):** Eksport wszystkich danych użytkownika do pliku JSON/CSV i łatwy import na innym urządzeniu.

---

## 2. H3 & Map Engine (Wizualizacja i Optymalizacje)

### 📈 Statystyki i Osiągnięcia (Grywalizacja)
* **Statystyki obszaru:** Obliczanie realnej powierzchni odwiedzonej na podstawie rozdzielczości heksów (np. "Odwiedziłeś już 124 km² świata!").
* **Podział na kraje:** Integracja z granicami państw/województw w formacie GeoJSON. Aplikacja mogłaby informować: "Odwiedziłeś 15% powierzchni Polski" lub "Odwiedziłeś 5 różnych państw".
* **Wizualna oś czasu (Time Slider):** Suwak na dole ekranu, który pozwala cofnąć się w czasie i animować, jak z miesiąca na miesiąc przybywało odwiedzonych heksów.

### 🗺️ Lepsze Podkłady Mapowe (Map Tiles)
* **Wybór mapy:** Dodanie przełącznika podkładów mapowych:
  * Widok satelitarny (np. Esri World Imagery) – genialny do weryfikacji leśnych i dzikich terenów.
  * Mapa fizyczna/topograficzna (np. OpenTopoMap) – idealna w góry.
  * Prosta, minimalistyczna mapa wektorowa.

### ⚡ Optymalizacja Renderowania przy Dużej Skali
* **Płynność działania:** Przy tysiącach zaznaczonych heksów standardowy Canvas Leaflet może zacząć spowalniać.
  * Rozwiązanie: Przejście na renderowanie z użyciem **Leaflet.VectorGrid** lub integracja z biblioteką **MapLibre GL** (WebGL), która bez problemu renderuje setki tysięcy obiektów.
* **Inteligentne pobieranie (Bounding Box):** Zamiast pobierać z bazy wszystkie heksy na raz podczas ładowania strony, API mogłoby zwracać tylko te heksy, które znajdują się w aktualnym oknie widoku mapy (`map.getBounds()`).

---

## 3. PWA i Działanie Offline (Offline-First)

### 💾 Zapisywanie Map w Pamięci Podręcznej
* **Pobieranie kafelków offline:** Możliwość zaznaczenia obszaru na mapie (np. "Bieszczady") i pobrania wszystkich kafelków mapy dla tego regionu do pamięci telefonu (przy użyciu IndexedDB). Dzięki temu mapa będzie działać w głębokim lesie lub górach całkowicie bez zasięgu.

### 🔄 Kolejka Synchronizacji (Offline Queue)
* **Zapis bez sieci:** Użytkownik podróżuje i klika heksy w trybie offline. Aplikacja zapisuje te akcje w lokalnej bazie `IndexedDB` w przeglądarce. Gdy telefon odzyska połączenie internetowe, Service Worker w tle (Background Sync API) wysyła zgromadzone dane do bazy SQLite na serwerze.

---

## 4. Backend i Bezpieczeństwo

### 🔐 Logowanie i Uwierzytelnianie
* **Autentykacja użytkowników:** Obecnie każdy może przełączyć się na dowolnego użytkownika w menu rozwijanym. Wprowadzenie prostego systemu logowania (hasło / token sesji) lub logowania przez Google/GitHub zapewni prywatność map.
* **Publiczne udostępnianie map:** Opcja generowania unikalnego linku (np. `api.php?share=token`), który pozwala innym osobom wyświetlić mapę danego podróżnika w trybie "tylko do odczytu".

### 📊 Integracja z zewnętrznymi API turystycznymi
* **Informacje o regionie:** Kliknięcie w heks pobiera ciekawostki turystyczne lub pogodę z zewnętrznych API (np. Wikipedia API, OpenWeatherMap) i wyświetla je w panelu bocznym.

---

## Sugerowane pierwsze kroki (Quick Wins)
Jeśli chcesz zacząć rozwijać projekt od zaraz, najszybsze i najbardziej efektowne wdrożenia to:
1. **Lokalizacja GPS:** Dodanie przycisku geolokalizacji zajmuje kilkanaście linii kodu w JS, a diametralnie poprawia wygodę na telefonach.
2. **Przycisk usuwania/edycji użytkowników:** Uporządkowanie podstawowego zarządzania profilami.
3. **Wybór mapy satelitarnej:** Wystarczy dodać nowy URL kafelków w `js/map.js` i dodać prosty przełącznik w UI.
