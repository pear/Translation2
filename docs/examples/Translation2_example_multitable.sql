CREATE TABLE i18n_de (
  string_id varchar(100) NOT NULL DEFAULT '',
  page_id varchar(100) DEFAULT NULL,
  string text,
  UNIQUE KEY id (string_id,page_id)
);

CREATE TABLE i18n_en (
  string_id varchar(100) NOT NULL DEFAULT '',
  page_id varchar(100) DEFAULT NULL,
  string text,
  UNIQUE KEY id (string_id,page_id)
);

CREATE TABLE i18n_it (
  string_id varchar(100) NOT NULL DEFAULT '',
  page_id varchar(100) DEFAULT NULL,
  string text,
  UNIQUE KEY id (string_id,page_id)
);

INSERT INTO i18n_en (string_id, page_id, string) VALUES ('smallTest',NULL,'very small test');
INSERT INTO i18n_de (string_id, page_id, string) VALUES ('smallTest',NULL,'kinder');
INSERT INTO i18n_it (string_id, page_id, string) VALUES ('smallTest',NULL,'piccolissimo test');


CREATE TABLE langs_avail (
  id varchar(10) NOT NULL DEFAULT '',
  name varchar(200) DEFAULT NULL,
  meta text,
  error_text varchar(250) DEFAULT NULL,
  encoding varchar(16) DEFAULT NULL,
  UNIQUE KEY ID (id)
);
INSERT INTO langs_avail (id, name, meta, error_text, encoding) VALUES ('it','italiano','charset: UTF-8','non disponibile in Italiano','UTF-8');
INSERT INTO langs_avail (id, name, meta, error_text, encoding) VALUES ('en','english','my meta info','not available in English','UTF-8');
INSERT INTO langs_avail (id, name, meta, error_text, encoding) VALUES ('de','deutsch','charset: UTF-8','kein Text auf Deutsch verfügbar','UTF-8');