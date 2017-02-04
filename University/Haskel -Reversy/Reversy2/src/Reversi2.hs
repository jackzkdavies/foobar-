module Reversi2 where

import Data.List

-- Position type and utility functions
type Position = (Int, Int)

-- ***
-- Given a Position value, determine whether or not it is a legal position on the board
isValidPos :: Position -> Bool
isValidPos (x, xs) 
        | x >= 8 = False
        | x < 0  = False
        | xs < 0 = False
        | xs >= 8 = False
        | otherwise = True


-- Player type and utility functions
data Player = PlayerWhite | PlayerBlack deriving (Eq)
instance Show Player where
        show PlayerWhite = "white"
        show PlayerBlack  = "black"

-- ***
-- Given a Player value, return the opponent player
otherPlayer :: Player -> Player
otherPlayer pl = if pl == PlayerWhite then PlayerBlack else PlayerWhite

-- Piece type and utility functions
data Piece = Piece Position Player deriving (Eq)
instance Show Piece where
        show (Piece _ PlayerWhite) = " W"
        show (Piece _ PlayerBlack) = " B"

-- ***
-- Given a Player value and a Piece value, does this piece belong to the player?
isPlayer :: Player -> Piece -> Bool
isPlayer pl (Piece a b) 
               |b == pl = True
               |otherwise = False
-- ***
-- Given a Piece value, determine who the piece belongs to
playerOf :: Piece -> Player
playerOf (Piece a b) = b
                
-- ***
-- Flip a piece over
flipPiece :: Piece -> Piece
flipPiece (Piece a b) =
                if b == PlayerWhite then Piece a PlayerBlack
                else  Piece a PlayerWhite
               
-- Board type and utility functions
type Board = [Piece]

-- The initial configuration of the game board
initialBoard :: Board
initialBoard =
        [
                Piece (3,4) PlayerWhite, Piece (4,4) PlayerBlack,
                Piece (3,3) PlayerBlack, Piece (4,3) PlayerWhite
        ]

-- ***
-- Given a Position value, is there a piece at that position?
isOccupied :: Position -> Board -> Bool
isOccupied pos [] = False
isOccupied pos ((Piece a b):xs)
                | pos == a = True
                | otherwise  = isOccupied pos xs
                
-- ***
-- Which piece is at a given position? 
-- Return Nothing in the case that there is no piece at the position
-- Otherwise return Just the_piece

index :: Position -> Board -> Int
index pos ((Piece a b):xs)
                | pos == a = 0
                | otherwise  = 1 + index pos xs

pieceAt :: Position -> Board -> Maybe Piece
pieceAt pos board 
        | isOccupied pos board  = Just p
        | otherwise = Nothing
        where 
         n = index pos board
         p = board !! n
 
-- ***
-- Determine if a particular piece can be placed on a board.  
-- There are two conditions: 
-- (1) no two pieces can occupy the same space, and 
-- (2) at least one of the other player's pieces must be flipped by the placement of the new piece.


validMove :: Piece -> Board -> Bool
validMove (Piece a b) board 
        | isValidPos a == False = False
        | isOccupied a board == True = False
        | (pieceAt a board == Nothing) && (toFlip (Piece a b) board /= [])  = True
        | otherwise = False

-- ***
-- Determine which pieces would be flipped by the placement of a new piece
toFlip :: Piece -> Board -> [Piece]
toFlip piece board = 
        
         flippable (getLineDir (1,0) piece board ) ++
         flippable (getLineDir (-1,0) piece board) ++
         flippable (getLineDir (0,1) piece board) ++
         flippable (getLineDir (0,-1) piece board) ++
         flippable (getLineDir (1,1) piece board) ++
         flippable (getLineDir (-1,-1) piece board) ++
         flippable (getLineDir (-1,1) piece board) ++
         flippable (getLineDir (1,-1) piece board)
        
-- ***
-- Auxillary function for toFlip. 
-- You don't have to use this function if you prefer to define toFlip some other way.
-- Determine which pieces might get flipped along a particular line 
-- when a new piece is placed on the board.  
-- The first argument is a vector (pair of integers) that describes 
-- the direction of the line to check.  
-- The second argument is the hypothetical new piece.  
-- The return value is either the empty list, 
-- a list where all pieces belong to the same player, 
-- or a list where the last piece belongs to the player of the hypothetical piece.  
-- Only in the last case can any of the pieces be flipped.

getLineDir :: (Int, Int) -> Piece -> Board -> [Piece]
getLineDir (a,b) (Piece (x,y) owner) board = case pieceAt (x+a, y+b) board of
        Nothing -> []
        Just (Piece v owner') -> if owner' == owner then [Piece v owner']   
                                 else (Piece v owner'):(getLineDir (a,b) (Piece (x+a,y+b) owner) board)



-- ***
-- Auxillary function for toFlip.
-- You don't have to use this function if you prefer to define toFlip some other way.
-- Given the output from getLineDir, determine which, if any, of the pieces would be flipped.

flippable :: [Piece] -> [Piece]
flippable [] = []
flippable (x:xs) 
                | length xs == 0 = []
                | playerOf x == playerOf n = []
                | otherwise = x:flippable xs
                where 
                 n = last xs
-- ***
-- Place a new piece on the board.  Assumes that it constitutes a validMove
flippingPieces :: [Piece] -> [Piece]
flippingPieces [] = []
flippingPieces (x:xs) 
                | length xs > 0 = flipPiece x : flippingPieces xs 
                | otherwise = [flipPiece x]
                

removingOldPieces ::  Board -> [Piece] -> [Piece]
removingOldPieces [] _ = []
removingOldPieces ((Piece a b):xs) pieces
                        |isOccupied a pieces =  removingOldPieces xs pieces     
                        | otherwise = (Piece a b) :  removingOldPieces xs pieces
                
                 
makeMove :: Piece -> Board -> Board
makeMove p board = if validMove p board then flippingPieces (toFlip p board) ++ newboard ++ [p] else []
                where newboard = removingOldPieces board (toFlip p board)
-- ***
-- Find all valid moves for a particular player

allMoves :: Player -> Board -> [Piece]
allMoves _ [] = []
allMoves player board = pieces
        where pieces = [(Piece (x,y) player)| x <- [0..8], y <- [0..8], validMove (Piece (x,y) player) board]  

-- ***
-- Count the number of pieces belonging to a player
score :: Player -> Board -> Int
score _ [] = 0
score player ((Piece a b):xs) 
                | player /= b = score player xs
                | otherwise = 1 + score player xs

                               
                
                
                        
-- ***
-- Decide whether or not the game is over. The game is over when neither player can make a validMove
isGameOver :: Board -> Bool
isGameOver board
        | (allMoves PlayerWhite board == []) && (allMoves PlayerBlack board == [] )= True
        | otherwise = False
-- ***
-- Find out who wins the game.  
-- Return Nothing in the case of a draw.
-- Otherwise return Just the_Player
winner :: Board -> Maybe Player
winner board 
                | score PlayerWhite board > score PlayerBlack board = Just PlayerWhite
                | score PlayerBlack board > score PlayerWhite board = Just PlayerBlack
                | otherwise =  Nothing
