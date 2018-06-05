-- MySQL Script generated by MySQL Workbench
-- Fre 08 Sep 2017 21:20:51 CEST
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema betting-pool
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema betting-pool
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `betting-pool` DEFAULT CHARACTER SET utf8 ;
USE `betting-pool` ;

-- -----------------------------------------------------
-- Table `betting-pool`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `betting-pool`.`user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `admin` TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `betting-pool`.`season`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `betting-pool`.`season` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `start_time` DATETIME NULL,
  `bet_type` ENUM('winner', 'result', 'result90') NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `betting-pool`.`matchday`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `betting-pool`.`matchday` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `season_id` INT NOT NULL,
  `start_time` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_matchday_season1_idx` (`season_id` ASC),
  CONSTRAINT `fk_matchday_season1`
    FOREIGN KEY (`season_id`)
    REFERENCES `betting-pool`.`season` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `betting-pool`.`match`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `betting-pool`.`match` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `matchday_id` INT NOT NULL,
  `url` VARCHAR(1023) NULL,
  `start_time` DATETIME NOT NULL,
  `finished` TINYINT(1) NOT NULL DEFAULT 0,
  `home_team` VARCHAR(255) NOT NULL,
  `home_logo` VARCHAR(1023) NULL,
  `guest_team` VARCHAR(255) NOT NULL,
  `guest_logo` VARCHAR(1023) NULL,
  `home_goals` INT NULL,
  `guest_goals` INT NULL,
  `winner` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_match_matchday_idx` (`matchday_id` ASC),
  CONSTRAINT `fk_match_matchday`
    FOREIGN KEY (`matchday_id`)
    REFERENCES `betting-pool`.`matchday` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `betting-pool`.`bet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `betting-pool`.`bet` (
  `user_id` INT NOT NULL,
  `match_id` INT NOT NULL,
  `bet` VARCHAR(45) NULL,
  `time` DATETIME NULL,
  `points` INT NULL,
  `submitted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`match_id`, `user_id`),
  INDEX `fk_bet_match1_idx` (`match_id` ASC),
  INDEX `fk_bet_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_bet_match1`
    FOREIGN KEY (`match_id`)
    REFERENCES `betting-pool`.`match` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_bet_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `betting-pool`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `betting-pool`.`betgroup`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `betting-pool`.`betgroup` (
  `id` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `betting-pool`.`betgroup-user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `betting-pool`.`betgroup-user` (
  `betgroup_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`betgroup_id`, `user_id`),
  INDEX `fk_betgroup-user_betgroup1_idx` (`betgroup_id` ASC),
  INDEX `fk_betgroup-user_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_betgroup-user_betgroup1`
  FOREIGN KEY (`betgroup_id`)
  REFERENCES `betting-pool`.`betgroup` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_betgroup-user_user1`
  FOREIGN KEY (`user_id`)
  REFERENCES `betting-pool`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `betting-pool`.`betgroup-season`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `betting-pool`.`betgroup-season` (
  `betgroup_id` INT NOT NULL,
  `season_id` INT NOT NULL,
  PRIMARY KEY (`betgroup_id`, `season_id`),
  INDEX `fk_betgroup-season_betgroup1_idx` (`betgroup_id` ASC),
  INDEX `fk_betgroup-season_season1_idx` (`season_id` ASC),
  CONSTRAINT `fk_betgroup-season_betgroup1`
  FOREIGN KEY (`betgroup_id`)
  REFERENCES `betting-pool`.`betgroup` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_betgroup-season_season1`
  FOREIGN KEY (`season_id`)
  REFERENCES `betting-pool`.`season` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
  ENGINE = InnoDB;