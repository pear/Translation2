CREATE TABLE mytable_i18n (
  ID varchar(50) NOT NULL,
  pageID varchar(100),
  en text,
  de text,
  es text,
  fr text,
  it text,
  UNIQUE KEY ID (ID, pageID)
);





INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_01", NULL, "january", NULL, NULL, NULL, "gennaio");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_02", NULL, "february", NULL, NULL, NULL, "febbraio");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_03", NULL, "march", NULL, NULL, NULL, "marzo");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_04", NULL, "april", NULL, NULL, NULL, "aprile");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_05", NULL, "may", NULL, NULL, NULL, "maggio");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_06", NULL, "june", NULL, NULL, NULL, "giugno");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_07", NULL, "july", NULL, NULL, NULL, "luglio");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_08", NULL, "august", NULL, NULL, NULL, "agosto");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_09", NULL, "september", NULL, NULL, NULL, "settembre");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_10", NULL, "october", NULL, NULL, NULL, "ottobre");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_11", NULL, "november", NULL, NULL, NULL, "novembre");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("month_12", NULL, "december", NULL, NULL, NULL, "dicembre");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("day_0", NULL, "sunday", NULL, NULL, NULL, "domenica");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("day_1", NULL, "monday", NULL, NULL, NULL, "lunedì");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("day_2", NULL, "tuesday", NULL, NULL, NULL, "martedì");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("day_3", NULL, "wednesday", NULL, NULL, NULL, "mercoledì");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("day_4", NULL, "thursday", NULL, NULL, NULL, "giovedì");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("day_5", NULL, "friday", NULL, NULL, NULL, "venerdì");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("day_6", NULL, "saturday", NULL, NULL, NULL, "sabato");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("only_english", NULL, "only english text", NULL, NULL, NULL, NULL);
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("only_italian", NULL, NULL, NULL, NULL, NULL, "testo solo in italiano");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("hello_user", NULL, "hello &&user&&, today is &&weekday&&, &&day&&th &&month&& &&year&&", NULL, NULL, NULL, "ciao, &&user&&, oggi è il &&day&& &&month&& &&year&& (&&weekday&&)");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("alone", "alone", "all alone", NULL, NULL, NULL, "solo soletto");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("isempty", NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("prova_conflitto", "in_page", "conflicting text - in page", NULL, NULL, NULL, "testo con conflitto - in page");
INSERT INTO mytable_i18n (ID, pageID, en, de, es, fr, it) VALUES("prova_conflitto", NULL, "conflicting text - Global", NULL, NULL, NULL, "testo con conflitto - globale");


CREATE TABLE mytable_langs_avail (
  ID varchar(10) NOT NULL,
  name varchar(200),
  meta text,
  error_text varchar(250),
  UNIQUE KEY ID (ID)
);

INSERT INTO mytable_langs_avail (ID, name, meta, error_text) VALUES("it", "italiano", "charset=iso-8859-1", "non disponibile");
INSERT INTO mytable_langs_avail (ID, name, meta, error_text) VALUES("en", "english", "my meta info", "not available");
