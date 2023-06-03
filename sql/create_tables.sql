CREATE DATABASE edr;
use edr;

CREATE TABLE customer_info(
    first_name varchar(40) not null,
    last_name varchar(40) not null,
    email varchar(40) unique not null,
    pass varchar(200) not null,
    unique_id varchar(200) unique not null,
    PRIMARY KEY (email)
);

CREATE TABLE endpoint_info(
    id int auto_increment not null,
    endpoint_name varchar(40) not null,
    mac_address varchar(20) unique not null,
    unique_id varchar(40) not null,
    PRIMARY KEY (id),
    FOREIGN KEY (unique_id) REFERENCES customer_info(unique_id)
);

CREATE TABLE endpoint_events(
    id int auto_increment not null,
    mac_address varchar(20) not null,
    event_id varchar(10) not null,
    record_number varchar(10) not null,
    source_name varchar(150) not null,
    event_description text not null,
    time_written text not null,
    extra_event_info text not null,
    event_status enum('1', '0') not null,
    PRIMARY KEY (id),
    FOREIGN KEY (mac_address) REFERENCES endpoint_info(mac_address)
);