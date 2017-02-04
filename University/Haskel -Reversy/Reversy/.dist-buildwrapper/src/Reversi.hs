module Reversi where

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
otherPlayer PlayerWhite = PlayerBlack
otherPlayer PlayerBlack = PlayerWhite

-- Piece type and utility functions
data Piece = Piece Position Player deriving (Eq)
instance Show Piece where
        show (Piece _ PlayerWhite) = " W"
        show (Piece _ PlayerBlack) = " B"

-- ***
-- Given a Player value and a Piece value, does this piece belong to the player?
isPlayer :: Player -> Piece -> Bool
isPlayer x (Piece y z) | x == z = True
                       | otherwise = False


-- ***
-- Given a Piece value, determine who the piece belongs to
playerOf :: Piece -> Player
playerOf (Piece y z) = z 

-- ***
-- Flip a piece over
flipPiece :: Piece -> Piece
flipPiece (Piece y z) = (Piece y (otherPlayer z))


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
isOccupied _ [] = False
isOccupied p ((Piece a c):xs) = if p == a then True else isOccupied p xs 

-- ***
-- Which piece is at a given position? 
-- Return Nothing in the case that there is no piece at the position
-- Otherwise return Just the_piece

pieceAt :: Position -> Board -> Maybe Piece
pieceAt p [] = Nothing
pieceAt p ((Piece a c):xs) = if p == a then Just (Piece a c) else pieceAt p xs
--
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
toFlip p b = flippable (getLineDir (1,0) p b ) ++
             flippable (getLineDir ((-1),0) p b) ++
             flippable (getLineDir (0,1) p b) ++
             flippable (getLineDir (0,(-1)) p b) ++
             flippable (getLineDir (1,1) p b) ++
             flippable (getLineDir ((-1),(-1)) p b) ++
             flippable (getLineDir ((-1),1) p b) ++
             flippable (getLineDir (1,(-1)) p b)

-- ***
-- Auxillary function for toFlip. 
-- You don't have to use this function if you prefer to define toFlip some other way.

-- ***do this one
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
flippable xs | length xs == 0 = []
             | length xs == 1 = []
             | playerOf (head xs) == playerOf (head (reverse xs)) = []
             | otherwise = tail (reverse xs)

flippedPieces :: [Piece] -> [Piece]
flippedPieces [] = []
flippedPieces (x:xs) = [flipPiece x] ++ flippedPieces xs

keepPiece :: Piece -> [Piece] -> Bool
keepPiece p [] = True
keepPiece p (x:xs) | p == x = False
                   | otherwise = keepPiece p xs

removePieces :: Board -> [Piece] -> Board
removePieces bs ps = [x | x <- bs, keepPiece x ps]

-- ***
-- Place a new piece on the board.  Assumes that it constitutes a validMove
makeMove :: Piece -> Board -> Board
makeMove p b = if validMove p b then [p] ++ flippedPieces (toFlip p b) ++ removePieces b (toFlip p b) else []


-- ***
-- Find all valid moves for a particular player
allMoves :: Player -> Board -> [Piece]
allMoves _ [] = []
allMoves player board = pieces
                        where pieces = [(Piece (x,y) player)| x <- [0..8], y <- [0..8], validMove (Piece (x,y) player) board]


-- ***
-- Count the number of pieces belonging to a player
score :: Player -> Board -> Int
score p [] = 0
score p (x:xs) = if isPlayer p x then 1 + score p xs else score p xs

-- ***
-- Decide whether or not the game is over. The game is over when neither player can make a validMove
isGameOver :: Board -> Bool
isGameOver b = if length (allMoves PlayerWhite b) == 0 && length (allMoves PlayerBlack b) == 0 then True else False
  

-- ***
-- Find out who wins the game.  
-- Return Nothing in the case of a draw.
-- Otherwise return Just the_Player
winner :: Board -> Maybe Player
winner b | score PlayerWhite b == score PlayerBlack b = Nothing
         | score PlayerWhite b > score PlayerBlack b = Just PlayerWhite
         | otherwise = Just PlayerBlack


