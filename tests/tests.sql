CREATE TABLE i18n (
  id TEXT NOT NULL,
  page_id varchar(100),
  en text,
  de text,
  it text
);

CREATE UNIQUE INDEX i18n_id_index ON i18n (id(16), page_id);

INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_01", "calendar", "january", NULL, "gennaio");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_02", "calendar", "february", NULL, "febbraio");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_03", "calendar", "march", NULL, "marzo");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_04", "calendar", "april", NULL, "aprile");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_05", "calendar", "may", NULL, "maggio");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_06", "calendar", "june", NULL, "giugno");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_07", "calendar", "july", NULL, "luglio");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_08", "calendar", "august", NULL, "agosto");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_09", "calendar", "september", NULL, "settembre");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_10", "calendar", "october", NULL, "ottobre");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_11", "calendar", "november", NULL, "novembre");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("month_12", "calendar", "december", NULL, "dicembre");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("day_0", "calendar", "sunday", NULL, "domenica");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("day_1", "calendar", "monday", NULL, "lunedì");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("day_2", "calendar", "tuesday", NULL, "martedì");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("day_3", "calendar", "wednesday", NULL, "mercoledì");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("day_4", "calendar", "thursday", NULL, "giovedì");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("day_5", "calendar", "friday", NULL, "venerdì");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("day_6", "calendar", "saturday", NULL, "sabato");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("only_english", NULL, "only english text", NULL, NULL);
INSERT INTO i18n (id, page_id, en, de, it) VALUES("only_italian", NULL, NULL, NULL, "testo solo in italiano");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("hello_user", NULL, "hello &&user&&, today is &&weekday&&, &&day&&th &&month&& &&year&&", NULL, "ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("alone", "alone", "all alone", NULL, "solo soletto");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("isempty", NULL, NULL, "this string is empty in English and Italian, but not in German!", NULL);
INSERT INTO i18n (id, page_id, en, de, it) VALUES("prova_conflitto", "in_page", "conflicting text - in page", NULL, "testo con conflitto - in page");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("prova_conflitto", NULL, "conflicting text - Global", NULL, "testo con conflitto - globale");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("pageid_vuoto", "", "string with empty pageID (i.e. NOT NULL)", NULL, "stringa con pageID vuoto ma non nullo");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("test", NULL, "this is a test string", NULL, "stringa di prova");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("bbcode", "bbcode", "[b]this text should be bold[/b],\r\n[i]italic[/i], \r\nhere\'s a link: http://www.server.com \r\nemail@address.com\r\n[email=email@address.com]write me![/email]", NULL, "[b]grassetto[/b],\r\n[i]corsivo[/i], \r\nun link: http://www.server.com \r\nemail@address.com\r\n[email=email@address.com]write me![/email]");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("Entirely new string", NULL, "Entirely new string", NULL, NULL);
INSERT INTO i18n (id, page_id, en, de, it) VALUES("Entirely new string", "de", "Entirely new string", NULL, NULL);
INSERT INTO i18n (id, page_id, en, de, it) VALUES("Entirely new string", "samplePage", "Entirely new string", NULL, NULL);
INSERT INTO i18n (id, page_id, en, de, it) VALUES("first string", "small page", "first string", NULL, "prima stringa");
INSERT INTO i18n (id, page_id, en, de, it) VALUES("second string", "small page", "second string", NULL, "seconda stringa");


CREATE TABLE langs_avail (
  id varchar(10) NOT NULL,
  name varchar(200),
  meta text,
  error_text varchar(250),
  encoding varchar(16) NOT NULL DEFAULT 'iso-8859-1'
);

CREATE UNIQUE INDEX langs_avail_id_index ON langs_avail (id);

INSERT INTO langs_avail (id, name, meta, error_text, encoding) VALUES("it", "italiano", "charset: iso-8859-1", "non disponibile in Italiano", "iso-8859-1");
INSERT INTO langs_avail (id, name, meta, error_text, encoding) VALUES("en", "english", "my meta info", "not available in English", "iso-8859-1");
INSERT INTO langs_avail (id, name, meta, error_text, encoding) VALUES("de", "deutsch", "charset: iso-8859-1", "kein Text auf Deutsch verfügbar", "iso-8859-1");
